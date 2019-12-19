<?php

namespace App\Model;

use App\Annotation\ModelVariable;
use App\Enum\NodeStatus;

class ParsedNode extends AbstractModel
{
    /**
     * @ModelVariable()
     */
    public $name;

    /**
     * @ModelVariable()
     */
    public $identifier;

    /**
     * @ModelVariable()
     */
    public $parser;

    /**
     * @ModelVariable()
     */
    public $level;

    /**
     * @ModelVariable()
     */
    public $nextLevel;

    /**
     * @ModelVariable()
     */
    public $description;

    /**
     * @ModelVariable()
     */
    public $url;

    /**
     * @ModelVariable()
     */
    public $ratio = 0;

    /**
     * @ModelVariable()
     */
    public $imagesNo = 0;

    /**
     * @ModelVariable()
     */
    public $commentsNo = 0;

    /**
     * @ModelVariable(type="array")
     */
    public $thumbnails = [];

    /**
     * @ModelVariable(type="array")
     */
    public $localThumbnails = [];

    /**
     * @ModelVariable(type="array")
     */
    public $statuses = [];

    /**
     * @ModelVariable(type="boolean")
     */
    public $noImage = false;

    /**
     * @ModelVariable(type="boolean")
     */
    public $queued = false;

    /**
     * @ModelVariable(type="boolean")
     */
    public $blocked = false;

    /**
     * @ModelVariable(type="boolean")
     */
    public $favorited;

    /**
     * @ModelVariable(type="boolean")
     */
    public $finished;

    private $statusesNames = [
        NodeStatus::Queued,
        NodeStatus::Blocked,
        NodeStatus::Favorited,
        NodeStatus::Finished
    ];

    public function __construct(string $parser = null, string $level = null)
    {
        if ($parser) {
            $this->setParser($parser);
        }

        if ($level) {
            $this->setLevel($level);

            // TODO: autodetect next level?
        }
    }

    public function emptyStatuses() : bool
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

    public function setStatusesFromArray() : self
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
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return $this;
     */
    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param mixed $identifier
     * @return $this;
     */
    public function setIdentifier($identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * @param mixed $parser
     * @return $this;
     */
    public function setParser($parser): self
    {
        $this->parser = $parser;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     * @return $this;
     */
    public function setLevel($level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNextLevel()
    {
        return $this->nextLevel;
    }

    /**
     * @param mixed $nextLevel
     * @return $this;
     */
    public function setNextLevel($nextLevel): self
    {
        $this->nextLevel = $nextLevel;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return $this;
     */
    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     * @return $this;
     */
    public function setUrl($url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRatio()
    {
        return $this->ratio;
    }

    /**
     * @param mixed $ratio
     * @return $this;
     */
    public function setRatio($ratio): self
    {
        $this->ratio = $ratio;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImagesNo()
    {
        return $this->imagesNo;
    }

    /**
     * @param mixed $imagesNo
     * @return $this;
     */
    public function setImagesNo($imagesNo): self
    {
        $this->imagesNo = $imagesNo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommentsNo()
    {
        return $this->commentsNo;
    }

    /**
     * @param mixed $commentsNo
     * @return $this;
     */
    public function setCommentsNo($commentsNo): self
    {
        $this->commentsNo = $commentsNo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getThumbnails()
    {
        return $this->thumbnails;
    }

    /**
     * @param mixed $thumbnails
     * @return $this;
     */
    public function setThumbnails($thumbnails): self
    {
        $this->thumbnails = $thumbnails;

        return $this;
    }

    public function addThumbnail(string $thumbnail) : self
    {
        $this->thumbnails[] = $thumbnail;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocalThumbnails()
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

    public function addLocalThumbnail(string $localThumbnail) : self
    {
        $this->localThumbnails[] = $localThumbnail;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatuses()
    {
        return $this->statuses;
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
     * @return mixed
     */
    public function getQueued()
    {
        return $this->queued;
    }

    /**
     * @param mixed $queued
     * @return $this
     */
    public function setQueued($queued): self
    {
        $this->queued = $queued;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBlocked()
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
     * @return mixed
     */
    public function getFavorited()
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
     * @return mixed
     */
    public function getFinished()
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
}