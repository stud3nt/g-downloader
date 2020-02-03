<?php

namespace App\Model;

use App\Annotation\ModelVariable;
use App\Converter\ModelConverter;
use App\Entity\User;

class ParserRequest extends AbstractModel
{
    /**
     * @var string
     * @ModelVariable()
     */
    public $actionName;

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
     * @ModelVariable()
     */
    public $requestIdentifier = null;

    public function __construct(User $user = null)
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
    public function setActionName(string $actionName): self
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
    public function setNextNode(ParsedNode $nextNode): self
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
    public function setPreviousNode(ParsedNode $previousNode): self
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
    public function setBreadcrumbNodes(array $breadcrumbNodes): self
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
    public function setFiles(array $files): self
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
    public function setFileData($fileData): ?self
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
    public function setTokens($tokens): self
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
    public function setIgnoreCache(bool $ignoreCache): self
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
    public function setCachedData(bool $cachedData): self
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

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestIdentifier(): string
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

        $this->getStatus()->setRequestIdentifier($requestIdentifier);

        return $this;
    }
}