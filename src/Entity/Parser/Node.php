<?php

namespace App\Entity\Parser;

use App\Annotation\EntityVariable;
use App\Entity\Base\AbstractEntity;
use App\Entity\Traits\{CreatedAtTrait, IdentifierTrait, NameTrait, ParserTrait, UpdatedAtTrait, UrlTrait};
use Doctrine\ORM\Mapping as ORM;

/**
 * Parsed nodes
 *
 * @ORM\Table(name="parsed_nodes", indexes={
 *     @ORM\Index(name="identifier_idx", columns={"identifier"}),
 *     @ORM\Index(name="url_idx", columns={"url"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\NodeRepository")
 */
class Node extends AbstractEntity
{
    use CreatedAtTrait,
        IdentifierTrait,
        NameTrait,
        ParserTrait,
        UpdatedAtTrait,
        UrlTrait;

    /**
     * @ORM\Column(name="level", type="string", length=20, nullable=false)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $level;

    /**
     * @ORM\Column(name="description", type="string", length=4096, nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="ratio", type="integer", length=6, options={"unsigned"=true, "default":0})
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $ratio = 0;

    /**
     * @ORM\Column(name="images_no", type="integer", options={"unsigned"=true, "default":0}, length=6)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $imagesNo = 0;

    /**
     * @ORM\Column(name="comments_no", type="integer", options={"unsigned"=true, "default":0}, length=4)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $commentsNo = 0;

    /**
     * @ORM\Column(name="thumbnails", type="array", nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $thumbnails = [];

    /**
     * @ORM\Column(name="local_thumbnails", type="array", nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $localThumbnails = [];

    /**
     * @var \DateTime
     * @ORM\Column(name="last_viewed_at", type="datetime", options={"default"="CURRENT_TIMESTAMP"}, nullable=true)
     * @EntityVariable(convertable=true, writable=true, inAllConvertNames=false, readable=true, converter="DateTime")
     */
    protected $lastViewedAt;

    /**
     * @ORM\Column(name="queued", type="boolean", length=1, options={"unsigned"=true, "default":0})
     * @EntityVariable(convertable=true, writable=true, readable=true, type="boolean")
     */
    protected $queued = false;

    /**
     * @ORM\Column(name="blocked", type="boolean", length=1, options={"unsigned"=true, "default":0})
     * @EntityVariable(convertable=true, writable=true, readable=true, type="boolean")
     */
    protected $blocked = false;

    /**
     * @ORM\Column(name="favorited", type="boolean", length=1, options={"unsigned"=true, "default":0})
     * @EntityVariable(convertable=true, writable=true, readable=true, type="boolean")
     */
    protected $favorited = false;

    /**
     * @ORM\Column(name="finished", type="boolean", length=1, options={"unsigned"=true, "default":0})
     * @EntityVariable(convertable=true, writable=true, readable=true, type="boolean")
     */
    protected $finished = false;

    public function __construct()
    {
        if (!$this->getId()) {
            $this->createdAt = new \DateTime();
            $this->lastViewedAt = new \DateTime();
        }

        $this->updatedAt = new \DateTime();
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getRatio(): ?int
    {
        return $this->ratio;
    }

    public function setRatio(int $ratio): self
    {
        $this->ratio = $ratio;

        return $this;
    }

    public function getImagesNo(): ?int
    {
        return $this->imagesNo;
    }

    public function setImagesNo(int $imagesNo): self
    {
        $this->imagesNo = $imagesNo;

        return $this;
    }

    public function getCommentsNo(): ?int
    {
        return $this->commentsNo;
    }

    public function setCommentsNo(int $commentsNo): self
    {
        $this->commentsNo = $commentsNo;

        return $this;
    }

    public function getThumbnails(): ?array
    {
        return $this->thumbnails;
    }

    public function setThumbnails(?array $thumbnails): self
    {
        $this->thumbnails = $thumbnails;

        return $this;
    }

    public function getLocalThumbnails(): ?array
    {
        return $this->localThumbnails;
    }

    public function setLocalThumbnails(?array $localThumbnails): self
    {
        $this->localThumbnails = $localThumbnails;

        return $this;
    }

    public function getLastViewedAt(): ?\DateTimeInterface
    {
        return $this->lastViewedAt;
    }

    public function setLastViewedAt(?\DateTimeInterface $lastViewedAt): self
    {
        $this->lastViewedAt = $lastViewedAt;

        return $this;
    }

    public function refreshLastViewedAt(): self
    {
        $this->lastViewedAt = new \DateTime();

        return $this;
    }

    public function getFavorited(): ?bool
    {
        return $this->favorited;
    }

    public function setFavorited(bool $favorited): self
    {
        $this->favorited = $favorited;

        return $this;
    }

    public function getFinished(): ?bool
    {
        return $this->finished;
    }

    public function setFinished(bool $finished): self
    {
        $this->finished = $finished;

        return $this;
    }

    public function getQueued(): ?bool
    {
        return $this->queued;
    }

    public function setQueued(bool $queued): self
    {
        $this->queued = $queued;

        return $this;
    }

    public function getBlocked(): ?bool
    {
        return $this->blocked;
    }

    public function setBlocked(bool $blocked): self
    {
        $this->blocked = $blocked;

        return $this;
    }

}
