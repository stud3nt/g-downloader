<?php

namespace App\Entity\Parser;

use App\Annotation\EntityVariable;
use App\Entity\Base\AbstractEntity;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Saved parsed nodes data;
 *
 * @ORM\Table(name="parsed_nodes_settings")
 * @ORM\Entity(repositoryClass="App\Repository\NodeSettingsRepository")
 */
class NodeSettings extends AbstractEntity
{
    use CreatedAtTrait,
        UpdatedAtTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", length=8, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @EntityVariable(convertable=true, writable=true, readable=true, inAllConvertNames=true)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var Node
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Parser\Node", inversedBy="settings", cascade={"persist"})
     * @ORM\JoinColumn(name="node_id", referencedColumnName="id")
     */
    protected $node;

    /**
     * @var string
     *
     * @ORM\Column(name="prefix", type="string", length=255, nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true, type="string")
     */
    protected $prefix = null;

    /**
     * @var string
     *
     * @ORM\Column(name="sufix", type="string", length=255, nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true, type="string")
     */
    protected $sufix = null;

    /**
     * @var string
     *
     * @ORM\Column(name="folder", type="string", length=255, nullable=true)
     * @EntityVariable(convertable=true, writable=true, readable=true, type="string")
     */
    protected $folder = null;

    /**
     * @var int
     *
     * @ORM\Column(name="max_size", type="integer", length=8, options={"default":0})
     * @EntityVariable(convertable=true, writable=true, readable=true, type="string")
     */
    protected $maxSize = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="max_width", type="integer", length=8, options={"default":0})
     * @EntityVariable(convertable=true, writable=true, readable=true, type="string")
     */
    protected $maxWidth = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="max_height", type="integer", length=8, options={"default":0})
     * @EntityVariable(convertable=true, writable=true, readable=true, type="string")
     */
    protected $maxHeight = 0;

    public function __construct()
    {
        if (!$this->getId()) {
            $this->createdAt = new \DateTime();
        }

        $this->updatedAt = new \DateTime();
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(?string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getSufix(): ?string
    {
        return $this->sufix;
    }

    public function setSufix(?string $sufix): self
    {
        $this->sufix = $sufix;

        return $this;
    }

    public function getFolder(): ?string
    {
        return $this->folder;
    }

    public function setFolder(?string $folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    public function getMaxSize(): ?int
    {
        return $this->maxSize;
    }

    public function setMaxSize(int $maxSize): self
    {
        $this->maxSize = $maxSize;

        return $this;
    }

    public function getMaxWidth(): ?int
    {
        return $this->maxWidth;
    }

    public function setMaxWidth(int $maxWidth): self
    {
        $this->maxWidth = $maxWidth;

        return $this;
    }

    public function getMaxHeight(): ?int
    {
        return $this->maxHeight;
    }

    public function setMaxHeight(int $maxHeight): self
    {
        $this->maxHeight = $maxHeight;

        return $this;
    }

    public function getNode(): ?Node
    {
        return $this->node;
    }

    public function setNode(?Node $node): self
    {
        $this->node = $node;

        return $this;
    }
}
