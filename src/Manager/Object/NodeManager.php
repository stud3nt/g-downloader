<?php

namespace App\Manager\Object;

use App\Converter\EntityConverter;
use App\Converter\ModelConverter;
use App\Entity\Parser\Node;
use App\Enum\NodeStatus;
use App\Manager\Base\EntityManager;
use App\Manager\CategoryManager;
use App\Manager\TagManager;
use App\Model\AbstractModel;
use App\Model\ParsedNode;
use App\Model\ParsedNodeSettings;
use App\Model\ParserRequest;
use App\Repository\NodeRepository;
use App\Utils\StringHelper;
use Doctrine\Common\Util\Debug;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use ReflectionException;

class NodeManager extends EntityManager
{
    protected $entityName = 'Parser\Node';

    /** @var NodeRepository */
    protected $repository;

    /** @var EntityConverter */
    protected $entityConverter;

    /** @var ModelConverter */
    protected $modelConverter;

    /** @var TagManager */
    protected $tagManager;

    /** @var CategoryManager */
    protected $categoryManager;

    public function __construct(ObjectManager $em, TokenStorageInterface $tokenStorage, EntityConverter $entityConverter, TagManager $tagManager, CategoryManager $categoryManager)
    {
        parent::__construct($em, $tokenStorage);

        $this->entityConverter = $entityConverter;
        $this->entityConverter->setEntityManager($em);
        $this->modelConverter = new ModelConverter();
        $this->modelConverter->setEntityManager($em);

        $this->tagManager = $tagManager;
        $this->categoryManager = $categoryManager;
    }

    public function getOneByParsedNode(ParsedNode $node): ?Node
    {
        return $this->repository->findOneByParsedNode($node);
    }

    /**
     * Completes node object with database data (if exists);
     *
     * @param ParserRequest|AbstractModel $parserRequest
     * @return ParserRequest
     * @throws ReflectionException
     */
    public function completeCurrentNodeDataFromDb(ParserRequest &$parserRequest): ParserRequest
    {
        $node = $parserRequest->getCurrentNode();

        if (!$node->getUrl()) { // node haven't specified url => node doesn't come from database;
            $savedNode = $this->repository->findOneByParsedNode($node);

            if ($savedNode) {
                $nodeArray = $this->entityConverter->convert($savedNode);
                $this->modelConverter->setData($nodeArray, $node);

                if (!$node->getSettings())
                    $node->setSettings(new ParsedNodeSettings());

                $parserRequest->currentNode = $node;
            }
        }

        return $parserRequest;
    }

    /**
     * Update existing node (or creates new if not exists);
     *
     * @param ParserRequest $parserRequest
     * @return ParserRequest
     * @throws ReflectionException
     */
    public function updateCurrentNode(ParserRequest &$parserRequest): ParserRequest
    {
        $currentNode = $parserRequest->getCurrentNode();

        if ($currentNode->hasMinimumEntityData()) {
            $savedNode = $this->repository->findOneByParsedNode($currentNode);

            if (!$savedNode) {
                $savedNode = new Node();
            }

            $this->entityConverter->setData($currentNode, $savedNode);
            $this->save($savedNode);
        }

        return $parserRequest;
    }

    /**
     * Loads and compares data in nodes from database.
     * Saves new nodes.
     *
     * @param ParserRequest|AbstractModel $parserRequest
     * @return ParserRequest
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function completeParsedNodes(ParserRequest &$parserRequest): ParserRequest
    {
        $parentNode = $parserRequest->getCurrentNode();
        $parentNodeEntity = $this->repository->findOneByParsedNode($parentNode);

        // complete statuses for parent node;
        if ($parentNodeEntity) {
            $parentNodeEntity->refreshLastViewedAt();
            $this->save($parentNodeEntity);
            $this->updateNodeStatuses($parentNode, $parentNodeEntity);
        }

        $parserRequest->setCurrentNode($parentNode);

        if ($parsedNodes = $parserRequest->getParsedNodes()) {
            $parsedNodesIdentifiers = [];
            $parsedNodesForSave = $parsedNodes;

            foreach ($parsedNodes as $parsedNode) { // collect identifiers
                $parsedNodesIdentifiers[] = $parsedNode->getIdentifier();
            }

            $savedNodes = $this->repository->findByParentAndIdentifiers($parentNodeEntity, $parsedNodesIdentifiers);

            if ($savedNodes) {
                $tagsModels = $this->tagManager->getTagsModels();
                $categoriesModels = $this->categoryManager->getCategoriesModels();

                /** @var Node $savedNode */
                /** @var ParsedNode $parsedNode */
                foreach ($parsedNodes as $parsedNodeKey => $parsedNode) {
                    foreach ($savedNodes as $savedNodeKey => $savedNode) { // update statuses
                        if ($savedNode->getIdentifier() == $parsedNode->getIdentifier()) {
                            unset($parsedNodesForSave[$parsedNodeKey]); // no need to save this node, update only;

                            if ($parentNodeEntity) {
                                $savedNode->setParentNode($parentNodeEntity);
                            }

                            $this->updateNodeStatuses($parsedNode, $savedNode);
                            $this->em->persist($savedNode);

                            $category = $savedNode->getCategory();
                            $tags = $savedNode->getTags();

                            $parsedNode->setPersonalDescription(
                                $savedNode->getPersonalDescription()
                            );
                            $parsedNode->setPersonalRating(
                                $savedNode->getPersonalRating()
                            );

                            if ($category) {
                                $parsedNode->setCategory(
                                    $categoriesModels[$category->getId()]
                                );
                            }

                            if ($tags) {
                                $parsedNode->clearTags();

                                foreach ($tags as $tag) {
                                    $parsedNode->addTag(
                                        $tagsModels[$tag->getId()]
                                    );
                                }
                            }

                            $parsedNodes[$parsedNodeKey] = $parsedNode;
                        }
                    }
                }

                // set and sort parsed nodes;
                $parserRequest->setParsedNodes($parsedNodes)->sortParsedNodesByStatus(['favorited' => 'DESC']);
            }

            if ($parsedNodesForSave) { // save new parsed nodes to database;
                foreach ($parsedNodesForSave as $parsedNodeForSave) {
                    $nodeEntity = new Node();
                    $nodeEntity->setParentNode($parentNodeEntity);

                    $this->entityConverter->setData($parsedNodeForSave, $nodeEntity);
                    $this->em->persist($nodeEntity);
                }
            }

            $this->em->flush();
        }

        return $parserRequest;
    }

    /**
     * @param ParsedNode $parsedNode
     * @param Node $savedNode
     * @throws \Exception
     */
    public function updateNodeStatuses(ParsedNode &$parsedNode, Node &$savedNode): void
    {
        if ($savedNode->getImagesNo() !== $parsedNode->getImagesNo()) {
            $savedNode->setImagesNo($parsedNode->getImagesNo());
            $savedNode->setRating($parsedNode->getRating());
            $savedNode->setCommentsNo($parsedNode->getCommentsNo());

            // more images? Adding 'new content' info;
            $parsedNode->addStatus(NodeStatus::NewContent);
        }

        $parsedNode->setLastViewedAt(
            $savedNode->getLastViewedAt()
        );

        foreach (NodeStatus::getData() as $status) {
            $statusGetter = 'get'.ucfirst(StringHelper::underscoreToCamelCase($status));
            $statusSetter = 'set'.ucfirst(StringHelper::underscoreToCamelCase($status));

            if (method_exists($savedNode, $statusGetter) && $savedNode->$statusGetter()) {
                $parsedNode->addStatus($status, true);
                $parsedNode->$statusSetter($savedNode->$statusGetter());
            }
        }
    }

    /**
     * Updates node in database. If node does'nt exists - creates them;
     *
     * @param array $nodeData
     * @return ParsedNode
     * @throws \ReflectionException
     */
    public function updateNodeInDatabase(ParsedNode $parsedNode, bool $updateNodeData = true): ParsedNode
    {
        $dbNode = $this->repository->findOneBy([
            'identifier' => $parsedNode->getIdentifier(),
            'parser' => $parsedNode->getParser(),
            'level' => $parsedNode->getLevel()
        ]);

        if (!$dbNode) {
            $dbNode = new Node();
            $lastViewedAt = null;
        } else {
            $lastViewedAt = $dbNode->getLastViewedAt();
        }

        $this->entityConverter->setData($parsedNode, $dbNode);

        $dbNode->setLastViewedAt($lastViewedAt);

        $this->save($dbNode);

        if ($updateNodeData)
            $this->modelConverter->setData($dbNode, $parsedNode, true);

        return $parsedNode;
    }
}
