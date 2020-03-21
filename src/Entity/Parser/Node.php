<?php

namespace App\Entity\Parser;

use App\Annotation\EntityVariable;
use App\Entity\Base\AbstractEntity;
use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\Traits\{CreatedAtTrait, IdentifierTrait, NameTrait, ParserTrait, UpdatedAtTrait, UrlTrait};
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Parsed nodes
 *
 * @ORM\Table(name="parsed_nodes", indexes={
 *     @ORM\Index(name="IDX__parsed_nodes__identifier", columns={"identifier"}),
 *     @ORM\Index(name="IDX__parsed_nodes__url", columns={"url"})
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
     * @ORM\Column(name="saved", type="boolean", length=1, options={"unsigned"=true, "default":0})
     * @EntityVariable(convertable=true, writable=true, readable=true, type="boolean")
     */
    protected $saved = false;

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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Parser\File", mappedBy="parentNode")
     */
    protected $files;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Parser\Node", mappedBy="parentNode")
     */
    private $childrenNodes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Parser\Node", inversedBy="childrenNodes")
     * @ORM\JoinColumn(name="parent_node_id", referencedColumnName="id")
     */
    private $parentNode;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", cascade={"persist"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * @EntityVariable(convertable=true, writable=true, readable=true, converter="Entity", converterOptions={"class":"App\Entity\Category"})
     */
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", cascade={"persist"})
     * @EntityVariable(convertable=true, writable=true, type="array", readable=true, converter="Entity", converterOptions={"class":"App\Entity\Tag"})
     * @ORM\JoinTable(name="parsed_nodes_tags",
     *      joinColumns={@ORM\JoinColumn(name="parsed_node_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     *  )
     */
    private $tags;

    public function __construct()
    {
        if (!$this->getId()) {
            $this->createdAt = new \DateTime();
            $this->lastViewedAt = new \DateTime();
        }

        $this->updatedAt = new \DateTime();
        $this->files = new ArrayCollection();
        $this->childrens = new ArrayCollection();
        $this->childrenNodes = new ArrayCollection();
        $this->tags = new ArrayCollection();
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

    public function getBlocked(): ?bool
    {
        return $this->blocked;
    }

    public function setBlocked(bool $blocked): self
    {
        $this->blocked = $blocked;

        return $this;
    }

    /**
     * @return Collection|File[]
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files[] = $file;
            $file->setParentNode($this);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
            // set the owning side to null (unless already changed)
            if ($file->getParentNode() === $this) {
                $file->setParentNode(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Node[]
     */
    public function getChildrenNodes(): Collection
    {
        return $this->childrenNodes;
    }

    public function addChildrenNode(Node $childrenNode): self
    {
        if (!$this->childrenNodes->contains($childrenNode)) {
            $this->childrenNodes[] = $childrenNode;
            $childrenNode->setParentNode($this);
        }

        return $this;
    }

    public function removeChildrenNode(Node $childrenNode): self
    {
        if ($this->childrenNodes->contains($childrenNode)) {
            $this->childrenNodes->removeElement($childrenNode);
            // set the owning side to null (unless already changed)
            if ($childrenNode->getParentNode() === $this) {
                $childrenNode->setParentNode(null);
            }
        }

        return $this;
    }

    public function getParentNode(): ?self
    {
        return $this->parentNode;
    }

    public function setParentNode(?self $parentNode): self
    {
        $this->parentNode = $parentNode;

        return $this;
    }

    public function getSaved(): ?bool
    {
        return $this->saved;
    }

    public function setSaved(bool $saved): self
    {
        $this->saved = $saved;

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @return $this
     */
    public function setTags($tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @param Tag $tag
     * @return Node
     */
    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

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
     * @param Category $category
     * @return Node
     */
    public function setCategory(Category $category = null): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @param Tag $tag
     * @return Node
     */
    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
        }

        return $this;
    }
}
