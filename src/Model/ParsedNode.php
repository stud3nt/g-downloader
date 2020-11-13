<?php

namespace App\Model;

use App\Annotation\Serializer\ObjectVariable;
use App\Enum\NodeStatus;
use App\Utils\DateTimeHelper;

class ParsedNode extends AbstractModel
{
    /**
     * @ObjectVariable(type="string")
     */
    public ?string $name = null;

    /**
     * @ObjectVariable(type="string")
     */
    public ?string $label = null;

    /**
     * @ObjectVariable(type="string")
     */
    public ?string $identifier = null;

    /**
     * @ObjectVariable(type="string")
     */
    public ?string $parser = null;

    /**
     * @ObjectVariable(type="string")
     */
    public ?string $level = null;

    /**
     * @ObjectVariable(type="string")
     */
    public ?string $description = null;

    /**
     * @ObjectVariable(type="string")
     */
    public ?string $personalDescription = null;

    /**
     * @ObjectVariable(type="string")
     */
    public ?string $url = null;

    /**
     * @ObjectVariable(type="integer")
     */
    public int $rating = 0;

    /**
     * @ObjectVariable(type="integer")
     */
    public int $personalRating = 0;

    /**
     * @ObjectVariable(type="integer")
     */
    public int $imagesNo = 0;

    /**
     * @ObjectVariable(type="integer")
     */
    public int $commentsNo = 0;

    /**
     * @ObjectVariable(type="array")
     */
    public ?array $thumbnails = [];

    /**
     * @ObjectVariable(type="array")
     */
    public ?array $localThumbnails = [];

    /**
     * @ObjectVariable(type="array")
     */
    public ?array $statuses = [];

    /**
     * @ObjectVariable(type="boolean")
     */
    public bool $noImage = false;

    /**
     * @ObjectVariable(type="boolean")
     */
    public bool $queued = false;

    /**
     * @ObjectVariable(type="boolean")
     */
    public bool $blocked = false;

    /**
     * @ObjectVariable(type="boolean")
     */
    public bool $favorited = false;

    /**
     * @ObjectVariable(type="boolean")
     */
    public bool $finished = false;

    /**
     * @ObjectVariable(type="integer")
     */
    public ?int $expirationTime = 0;

    /**
     * @ObjectVariable(type="string")
     */
    public $lastViewedAt = null;

    /**
     * @ObjectVariable(type="boolean")
     */
    public bool $allowCategory = false;

    /**
     * @ObjectVariable(type="boolean")
     */
    public bool $allowTags = false;

    /**
     * @ObjectVariable(class="App\Model\Category")
     */
    public ?Category $category = null;

    /**
     * @var Tag[]|null
     * @ObjectVariable(class="App\Model\Tag[]")
     */
    public array $tags = [];

    /**
     * @ObjectVariable(class="App\Model\ParsedNodeSettings")
     */
    public ?ParsedNodeSettings $settings = null;

    private $statusesNames = [
        NodeStatus::Queued,
        NodeStatus::Blocked,
        NodeStatus::Favorited,
        NodeStatus::Finished
    ];

    public function __construct(string $parser = null, string $level = null, string $identifier = null)
    {
        $this->setParser($parser);
        $this->setLevel($level);
        $this->setIdentifier($identifier);
    }

    /**
     * Checks if ParsedNode has defined minimum data for creating/saving Node entity;
     *
     * @return bool
     */
    public function hasMinimumEntityData(): bool
    {
        foreach (['name', 'url', 'identifier', 'parser', 'level'] as $requiredField) {
            $getter = 'get'.ucfirst($requiredField);

            if ($this->$getter() === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function emptyStatuses(): bool
    {
        if (!empty($this->statuses))
            return false;

        foreach ($this->statusesNames as $statusName) {
            $statusGetter = 'get'.ucfirst($statusName);

            if (method_exists($this, $statusGetter) && $this->$statusGetter())
                return true;
        }

        return false;
    }

    /**
     * @return ParsedNode
     */
    public function setStatusesFromArray(): self
    {
        if ($this->getStatuses()) {
            foreach ($this->statusesNames as $statusName) {
                $statusSetter = 'set'.ucfirst($statusName);

                if (method_exists($this, $statusSetter))
                    $this->$statusSetter(
                        in_array($statusName, $this->statuses)
                    );
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this;
     */
    public function setName(string $name = null, bool $skipIfNotNull = false): self
    {
        $this->name = ($skipIfNotNull && $this->name) ? $this->name : $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this;
     */
    public function setLabel(string $label = null): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return $this;
     */
    public function setIdentifier(string $identifier = null): self
    {
        $this->identifier = preg_replace('/[^a-zA-Z0-9\-\_]/', '_', $identifier);

        return $this;
    }

    /**
     * @return string
     */
    public function getParser(): ?string
    {
        return $this->parser;
    }

    /**
     * @return $this;
     */
    public function setParser(?string $parser = null): self
    {
        $this->parser = $parser;

        return $this;
    }

    /**
     * @return string
     */
    public function getLevel(): ?string
    {
        return $this->level;
    }

    /**
     * @param string $level
     * @return $this;
     */
    public function setLevel($level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this;
     */
    public function setDescription(string $description = null): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this;
     */
    public function setUrl(string $url = null, bool $skipIfNotNull = false): self
    {
        $this->url = ($skipIfNotNull && $this->url) ? $this->url : $url;

        return $this;
    }

    /**
     * @return int
     */
    public function getImagesNo(): int
    {
        return $this->imagesNo;
    }

    /**
     * @param int $imagesNo
     * @return $this;
     */
    public function setImagesNo(int $imagesNo = 0): self
    {
        $this->imagesNo = $imagesNo;

        return $this;
    }

    /**
     * @return int
     */
    public function getCommentsNo(): int
    {
        return $this->commentsNo;
    }

    /**
     * @param int $commentsNo
     * @return $this;
     */
    public function setCommentsNo(int $commentsNo = 0): self
    {
        $this->commentsNo = $commentsNo;

        return $this;
    }

    /**
     * @return array
     */
    public function getThumbnails(): array
    {
        return $this->thumbnails;
    }

    /**
     * @param array $thumbnails
     * @return $this;
     */
    public function setThumbnails(array $thumbnails = []): self
    {
        $this->thumbnails = $thumbnails;

        return $this;
    }

    /**
     * @param string $thumbnail
     * @return ParsedNode
     */
    public function addThumbnail(string $thumbnail): self
    {
        $this->thumbnails[] = $thumbnail;

        return $this;
    }

    /**
     * @return array
     */
    public function getLocalThumbnails(): array
    {
        return $this->localThumbnails;
    }

    /**
     * @param mixed $localThumbnails
     * @return $this;
     */
    public function setLocalThumbnails($localThumbnails): self
    {
        $this->localThumbnails = $localThumbnails;

        return $this;
    }

    /**
     * @param string $localThumbnail
     * @return ParsedNode
     */
    public function addLocalThumbnail(string $localThumbnail) : self
    {
        $this->localThumbnails[] = $localThumbnail;

        return $this;
    }

    /**
     * @return array
     */
    public function getStatuses(): ?array
    {
        return $this->statuses;
    }

    /**
     * @param string $status
     * @param bool $unique - param is unique?
     * @return ParsedNode
     */
    public function addStatus(string $status, bool $unique = true): self
    {
        if (!$unique || !$this->hasStatus($status))
            $this->statuses[] = $status;

        return $this;
    }

    /**
     * @param string $checkedStatus
     * @return bool
     */
    public function hasStatus(string $checkedStatus): bool
    {
        if ($this->statuses) {
            foreach ($this->statuses as $status) {
                if ($status == $checkedStatus) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param mixed $statuses
     * @return $this;
     */
    public function setStatuses($statuses): self
    {
        $this->statuses = $statuses;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNoImage()
    {
        return $this->noImage;
    }

    /**
     * @param mixed $noImage
     * @return $this
     */
    public function setNoImage($noImage): self
    {
        $this->noImage = $noImage;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getQueued(): bool
    {
        return $this->queued;
    }

    /**
     * @return bool
     */
    public function isQueued(): bool
    {
        return $this->queued;
    }

    /**
     * @param bool $queued
     * @return $this
     */
    public function setQueued(bool $queued): self
    {
        $this->queued = $queued;

        return $this;
    }

    /**
     * @return bool
     */
    public function getBlocked(): bool
    {
        return $this->blocked;
    }

    /**
     * @param mixed $blocked
     * @return $this
     */
    public function setBlocked($blocked): self
    {
        $this->blocked = $blocked;

        return $this;
    }

    /**
     * @return bool
     */
    public function getFavorited(): bool
    {
        return $this->favorited;
    }

    /**
     * @return bool
     */
    public function isFavorited(): bool
    {
        return $this->favorited;
    }

    /**
     * @param mixed $favorited
     * @return $this
     */
    public function setFavorited($favorited): self
    {
        $this->favorited = $favorited;

        return $this;
    }

    /**
     * @return bool
     */
    public function getFinished(): bool
    {
        return $this->finished;
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->finished;
    }

    /**
     * @param mixed $finished
     * @return $this
     */
    public function setFinished($finished): self
    {
        $this->finished = $finished;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastViewedAt()
    {
        return $this->lastViewedAt;
    }

    /**
     * @param mixed $lastViewedAt
     * @return $this;
     * @throws \Exception
     */
    public function setLastViewedAt($lastViewedAt): self
    {
        if ($lastViewedAt instanceof \DateTime)
            $this->lastViewedAt = DateTimeHelper::dateDifference($lastViewedAt);
        else
            $this->lastViewedAt = $lastViewedAt;

        return $this;
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category|null $category
     * @return $this;
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Tag[]|null
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $tag
     * @return $this;
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

    /**
     * @return ParsedNode
     */
    public function clearTags(): self
    {
        $this->tags = [];

        return $this;
    }

    /**
     * @param mixed $tags
     * @return $this;
     */
    public function setTags($tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowCategory(): bool
    {
        return $this->allowCategory;
    }

    /**
     * @param bool $allowCategory
     * @return $this
     */
    public function setAllowCategory(bool $allowCategory): self
    {
        $this->allowCategory = $allowCategory;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowTags(): bool
    {
        return $this->allowTags;
    }

    /**
     * @param bool $allowTags
     * @return $this
     */
    public function setAllowTags(bool $allowTags): self
    {
        $this->allowTags = $allowTags;

        return $this;
    }

    /**
     * @return int
     */
    public function getRating(): int
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     * @return $this
     */
    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return string
     */
    public function getPersonalDescription(): ?string
    {
        return $this->personalDescription;
    }

    /**
     * @param string $personalDescription
     * @return $this
     */
    public function setPersonalDescription(string $personalDescription = null): self
    {
        $this->personalDescription = $personalDescription;

        return $this;
    }

    /**
     * @return int
     */
    public function getPersonalRating(): int
    {
        return $this->personalRating;
    }

    /**
     * @param int $personalRating
     * @return $this
     */
    public function setPersonalRating(int $personalRating): self
    {
        $this->personalRating = $personalRating;

        return $this;
    }

    /**
     * @return ParsedNodeSettings|null
     */
    public function getSettings(): ?ParsedNodeSettings
    {
        return $this->settings;
    }

    /**
     * @param ParsedNodeSettings $settings
     * @return $this
     */
    public function setSettings(ParsedNodeSettings $settings = null): self
    {
        $this->settings = $settings;

        return $this;
    }
}