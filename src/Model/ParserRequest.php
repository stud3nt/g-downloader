<?php

namespace App\Model;

use App\Annotation\Serializer\ObjectVariable;
use App\Model\Interfaces\StatusInterface;

class ParserRequest extends AbstractModel implements StatusInterface
{
    /**
     * @ObjectVariable(type="string")
     */
    public ?string $actionName = null;

    /**
     * @ObjectVariable(type="string")
     */
    public ?string $apiToken = null;

    /**
     * @ObjectVariable(class="App\Model\ParsedNode")
     */
    public ?ParsedNode $currentNode = null;

    /**
     * @ObjectVariable(class="App\Model\ParsedNode[]")
     */
    public ?array $parsedNodes = [];

    /**
     * @ObjectVariable(class="App\Model\ParsedNode[]")
     * @var ParsedNode[]
     */
    public ?array $breadcrumbNodes = [];

    /**
     * @ObjectVariable(class="App\Model\ParsedFile[]")
     * @var ParsedFile[]
     */
    public ?array $files = [];

    /**
     * @ObjectVariable(type="stdClass")
     */
    public ?\stdClass $fileData = null;

    /**
     * @ObjectVariable(class="App\Model\Pagination")
     */
    public Pagination $pagination;

    /**
     * @ObjectVariable(type="stdClass")
     */
    public ?\stdClass $tokens = null;

    /**
     * @ObjectVariable(type="boolean")
     */
    public bool $ignoreCache = false;

    /**
     * @ObjectVariable(type="boolean")
     */
    public bool $cachedData = false;

    /**
     * @ObjectVariable(type="array")
     */
    public array $sorting = [];

    /**
     * @ObjectVariable(class="App\Model\Status")
     */
    public Status $status;

    /**
     * @ObjectVariable(type="string")
     */
    public ?string $requestIdentifier = null;

    /**
     * @ObjectVariable(class="App\Model\Category[]")
     */
    public ?iterable $categories = [];

    /**
     * @ObjectVariable(class="App\Model\Tag[]")
     */
    public ?iterable $tags = [];

    public function __construct()
    {
        $this->clearData();
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

                        case 'desc':
                            return ($node1Value > $node2Value) ? -1 : 1;
                    }

                    return 0;
                });
            }
        }

        return $this;
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