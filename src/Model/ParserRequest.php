<?php

namespace App\Model;

use App\Annotation\ModelVariable;
use App\Converter\ModelConverter;
use App\Model\Interfaces\StatusInterface;

class ParserRequest extends AbstractModel implements StatusInterface
{
    /**
     * @var string
     * @ModelVariable()
     */
    public $actionName;

    /**
     * @var string
     * @ModelVariable()
     */
    public $apiToken;

    /**
     * @var ParsedNode
     * @ModelVariable(converter="Model", converterOptions={"class":"App\Model\ParsedNode"})
     */
    public $currentNode = null;

    /**
     * @ModelVariable(converter="Model", converterOptions={"class":"App\Model\ParsedNode"}, type="array")
     * @var ParsedNode[]
     */
    public $parsedNodes;

    /**
     * @var ParsedNode
     * @ModelVariable(converter="Model", converterOptions={"class":"App\Model\ParsedNode"})
     */
    public $nextNode = null;

    /**
     * @var ParsedNode
     * @ModelVariable(converter="Model", converterOptions={"class":"App\Model\ParsedNode"})
     */
    public $previousNode = null;

    /**
     * @ModelVariable(converter="Model", converterOptions={"class":"App\Model\ParsedNode"}, type="array")
     * @var ParsedNode[]
     */
    public $breadcrumbNodes = [];

    /**
     * @ModelVariable(converter="Model", converterOptions={"class":"App\Model\ParsedFile"}, type="array")
     * @var ParsedFile[]
     */
    public $files = [];

    /**
     * @ModelVariable(type="stdClass")
     */
    public $fileData;

    /**
     * @var Pagination
     * @ModelVariable(converter="Model", converterOptions={"class":"App\Model\Pagination"})
     */
    public $pagination;

    /**
     * @var \stdClass
     * @ModelVariable(type="stdClass")
     */
    public $tokens;

    /**
     * @var boolean
     * @ModelVariable(type="boolean")
     */
    public $ignoreCache = false;

    /**
     * @var boolean
     * @ModelVariable()
     */
    public $cachedData = false;

    /**
     * @var array
     * @ModelVariable(type="array")
     */
    public $sorting = [];

    /**
     * @var Status
     * @ModelVariable(converter="Model", converterOptions={"class":"App\Model\Status"})
     */
    public $status;

    /**
     * @var string
     * @ModelVariable(type="string")
     */
    public $requestIdentifier = null;

    /**
     * @var array
     * @ModelVariable(converter="Model", converterOptions={"class":"App\Model\Category"}, type="array")
     */
    public $categories = [];

    /**
     * @var array
     * @ModelVariable(converter="Model", converterOptions={"class":"App\Model\Tag"}, type="array")
     */
    public $tags = [];

    public function __construct()
    {
        $this->clearData();

        $this->modelConverter = new ModelConverter();
        $this->status = new Status();
    }

    /**
     * @return ParserRequest
     */
    public function clearData(): self
    {
        $this->data = new \stdClass();
        $this->tokens = new \stdClass();
        $this->letteration = new \stdClass();
        $this->parsedNodes = [];
        $this->files = [];

        $this->tokens->before = null;
        $this->tokens->after = null;

        $this->pagination = new Pagination();

        return $this;
    }

    /**
     * @return ParserRequest
     */
    public function clearParsedData(): self
    {
        $this->files = [];
        $this->parsedNodes = [];

        return $this;
    }

    /**
     * @return string
     */
    public function getActionName(): ?string
    {
        return $this->actionName;
    }

    /**
     * @param string $actionName
     * @return self
     */
    public function setActionName(string $actionName = null): self
    {
        $this->actionName = $actionName;

        return $this;
    }

    /**
     * @return ParsedNode
     */
    public function getCurrentNode(): ?ParsedNode
    {
        return $this->currentNode;
    }

    /**
     * @param ParsedNode $currentNode
     * @return self
     */
    public function setCurrentNode(ParsedNode $currentNode): self
    {
        $this->currentNode = $currentNode;

        return $this;
    }

    /**
     * @return ParsedNode[]
     */
    public function getParsedNodes(): ?array
    {
        return $this->parsedNodes;
    }

    /**
     * @param ParsedNode[] $parsedNodes
     * @return self
     */
    public function setParsedNodes(array $parsedNodes): self
    {
        $this->parsedNodes = $parsedNodes;

        return $this;
    }

    /**
     * @return ParserRequest
     */
    public function clearParsedNodes(): self
    {
        $this->parsedNodes = [];

        return $this;
    }

    /**
     * @param ParsedNode $parsedNode
     * @return ParserRequest
     */
    public function addParsedNode(ParsedNode $parsedNode): self
    {
        $this->parsedNodes[] = $parsedNode;

        return $this;
    }

    public function sortParsedNodesByStatus(array $sortingSettings = []): self
    {
        if (!empty($this->parsedNodes) && !empty($sortingSettings)) {
            foreach ($sortingSettings as $sortingColumn => $sortingValue) {
                $sortingData = [
                    'column' => $sortingColumn,
                    'value' => $sortingValue
                ];

                usort($this->parsedNodes, function(ParsedNode $node1, ParsedNode $node2) use ($sortingData) : int { // sorting nodes - favorites on top
                    $getter = 'get'.ucfirst($sortingData['column']);
                    $isser = 'is'.ucfirst($sortingData['column']);

                    if (method_exists($node1, $getter) && method_exists($node1, $getter)) {
                        $node1Value = (int)$node1->$getter();
                        $node2Value = (int)$node2->$getter();
                    } elseif (method_exists($node1, $isser) && method_exists($node2, $isser)) {
                        $node1Value = (int)$node1->$isser();
                        $node2Value = (int)$node2->$isser();
                    } else {
                        return 0;
                    }

                    if ($node1Value === $node2Value)
                        return 0;

                    switch (strtolower($sortingData['value'])) {
                        case 'asc':
                            return ($node1Value < $node2Value) ? -1 : 1;
                            break;

                        case 'desc':
                            return ($node1Value > $node2Value) ? -1 : 1;
                            break;
                    }

                    return 0;
                });
            }
        }

        return $this;
    }

    /**
     * @return ParsedNode
     */
    public function getNextNode(): ParsedNode
    {
        return $this->nextNode;
    }

    /**
     * @param ParsedNode $nextNode
     * @return self
     */
    public function setNextNode(ParsedNode $nextNode = null): self
    {
        $this->nextNode = $nextNode;

        return $this;
    }

    /**
     * @return ParsedNode
     */
    public function getPreviousNode(): ?ParsedNode
    {
        return $this->previousNode;
    }

    /**
     * @param ParsedNode $previousNode
     * @return self
     */
    public function setPreviousNode(ParsedNode $previousNode = null): self
    {
        $this->previousNode = $previousNode;

        return $this;
    }

    /**
     * @return ParsedNode[]
     */
    public function getBreadcrumbNodes(): ?array
    {
        return $this->breadcrumbNodes;
    }

    /**
     * @param ParsedNode[] $breadcrumbNodes
     * @return self
     */
    public function setBreadcrumbNodes(array $breadcrumbNodes = null): self
    {
        $this->breadcrumbNodes = $breadcrumbNodes;

        return $this;
    }

    /**
     * @param ParsedNode $node
     * @return ParserRequest
     */
    public function addBreadcrumbNode(ParsedNode $node): self
    {
        $this->breadcrumbNodes[] = $node;

        return $this;
    }

    /**
     * @return ParsedFile[]
     */
    public function getFiles(): ?array
    {
        return $this->files;
    }

    /**
     * @param ParsedFile[] $files
     * @return self
     */
    public function setFiles(array $files = null): self
    {
        $this->files = $files;

        return $this;
    }


    public function addFile(ParsedFile $file): self
    {
        $this->files[] = $file;

        return $this;
    }

    /**
     * @return ParserRequest
     */
    public function clearFiles(): self
    {
        $this->files = [];

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFileData()
    {
        return $this->fileData;
    }

    /**
     * @param mixed $fileData
     * @return self
     */
    public function setFileData($fileData = null): ?self
    {
        $this->fileData = $fileData;

        return $this;
    }

    /**
     * @return Pagination
     */
    public function getPagination(): Pagination
    {
        return $this->pagination;
    }

    /**
     * @param Pagination $pagination
     * @return self
     */
    public function setPagination(Pagination $pagination): self
    {
        $this->pagination = $pagination;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @param mixed $tokens
     * @return self
     */
    public function setTokens($tokens = null): self
    {
        $this->tokens = $tokens;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIgnoreCache(): bool
    {
        return $this->ignoreCache;
    }

    /**
     * @param bool $ignoreCache
     * @return self
     */
    public function setIgnoreCache(bool $ignoreCache = false): self
    {
        $this->ignoreCache = $ignoreCache;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCachedData(): bool
    {
        return $this->cachedData;
    }

    /**
     * @param bool $cachedData
     * @return self
     */
    public function setCachedData(bool $cachedData = false): self
    {
        $this->cachedData = $cachedData;

        return $this;
    }

    /**
     * @return array
     */
    public function getSorting(): ?array
    {
        return $this->sorting;
    }

    /**
     * @param array $sorting
     * @return self
     */
    public function setSorting(array $sorting): self
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * @return Status
     */
    public function getStatus(): Status
    {
        return $this->status;
    }

    /**
     * @param Status $status
     * @return $this;
     */
    public function setStatus(Status $status): self
    {
        $this->status = $status;

        if ($this->getRequestIdentifier())
            $this->getStatus()->setRequestIdentifier(
                $this->getRequestIdentifier()
            );


        return $this;
    }

    /**
     * @return string|null
     */
    public function getRequestIdentifier(): ?string
    {
        return $this->requestIdentifier;
    }

    /**
     * @param string $requestIdentifier
     * @return $this;
     */
    public function setRequestIdentifier(string $requestIdentifier): self
    {
        $this->requestIdentifier = $requestIdentifier;
        $this->status->setRequestIdentifier($requestIdentifier);

        return $this;
    }

    /**
     * @return string
     */
    public function getApiToken(): string
    {
        return $this->apiToken;
    }

    /**
     * @param string $apiToken
     * @return $this
     */
    public function setApiToken(string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     * @return $this
     */
    public function setCategories(array $categories = []): self
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @param mixed $category
     * @return $this
     */
    public function addCategory($category): self
    {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * @param mixed $category
     * @return $this
     */
    public function removeCategory($category): self
    {
        if (false !== $key = array_search($category, $this->categories, true)) {
            array_splice($this->categories, $key, 1);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     * @return $this
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @param mixed $tag
     * @return $this
     */
    public function addTag($tag): self
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * @param mixed $tag
     * @return $this
     */
    public function removeTag($tag): self
    {
        if (false !== $key = array_search($tag, $this->tags, true)) {
            array_splice($this->tags, $key, 1);
        }

        return $this;
    }
}