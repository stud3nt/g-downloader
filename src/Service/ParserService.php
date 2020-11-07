<?php


namespace App\Service;

use App\Entity\User;
use App\Enum\NodeLevel;
use App\Enum\ParserType;
use App\Factory\RedisFactory;
use App\Manager\Object\NodeManager;
use App\Manager\SettingsManager;
use App\Model\AbstractModel;
use App\Model\ParserRequest;
use App\Parser\Base\ParserInterface;
use App\Utils\StringHelper;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ParserService
{
    /** @var SettingsManager */
    protected $settingsManager;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var NodeManager */
    protected $nodeManager;

    public function __construct(SettingsManager $settingsManager, NodeManager $nodeManager, TokenStorageInterface $tokenStorage)
    {
        $this->settingsManager = $settingsManager;
        $this->tokenStorage = $tokenStorage;
        $this->redis = (new RedisFactory())->initializeConnection();
        $this->nodeManager = $nodeManager;
    }

    /**
     * @param ParserRequest|AbstractModel $parserRequest
     * @param User $user
     * @return ParserRequest
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function executeRequestedAction(ParserRequest $parserRequest, User $user): ParserRequest
    {
        $parser = $this->loadParser(
            $parserRequest->getCurrentNode()->getParser(), $user
        );

        if ($parser) { // parser found
            $parserRequest->clearParsedData();

            switch ($parserRequest->getCurrentNode()->getLevel()) { // execute parser action - load nodes or files;
                case NodeLevel::Owner:
                    $parser->getOwnersList($parserRequest);
                    break;

                case NodeLevel::BoardsList:
                    $parser->getBoardsListData($parserRequest);
                    break;

                case NodeLevel::Board:
                    $parser->getBoardData($parserRequest);
                    break;

                case NodeLevel::Gallery:
                    $parser->getGalleryData($parserRequest);
                    break;
            }
        }

        if (!$parserRequest->getCurrentNode()->getName())
            $parserRequest->getCurrentNode()->setName(
                $this->generateCurrentNodeName($parserRequest)
            );

        return $parserRequest;
    }

    /**
     * @param $parser
     * @param User $user
     * @return ParserInterface|array|null
     */
    public function loadParser($parser, User $user)
    {
        if (is_array($parser)) {
            $array = [];

            foreach ($parser as $parserName) {
                if ($parser = $this->parserFactory($parserName, $user))
                    $array[] = $parser;
            }

            return $array;
        } else {
            return $this->parserFactory($parser, $user);
        }
    }

    public function parserFactory(string $parserName, User $user): ?ParserInterface
    {
        $parserClass = 'App\\Parser\\'.ucfirst(StringHelper::underscoreToCamelCase($parserName)).'Parser';
        $parserSettings = $this->settingsManager->getParserSettings($parserName);

        $instance = class_exists($parserClass)
            ? new $parserClass($parserSettings, $user)
            : null;

        if ($instance) {
            $instance->setNodeManager($this->nodeManager);
        }

        return $instance;
    }

    protected function generateCurrentNodeName(ParserRequest $parserRequest): string
    {
        $currentNode = $parserRequest->getCurrentNode();
        $parserData = ParserType::getData();
        $parserName = (array_key_exists($currentNode->getParser(), $parserData))
            ? mb_strtoupper($parserData[$currentNode->getParser()]).':'
            : '';

        return $parserName.' '.mb_strtoupper(
            NodeLevel::getLevelName(
                $currentNode->getLevel()
            )
        );
    }
}