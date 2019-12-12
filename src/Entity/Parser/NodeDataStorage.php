<?php

namespace App\Entity\Parser;

use App\Annotation\EntityVariable;
use App\Entity\Base\AbstractEntity;
use App\Entity\Traits\IdentifierTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Saved parsed nodes data;
 *
 * @ORM\Table(name="parsed_nodes_data", indexes={
 *     @ORM\Index(name="identifier_idx", columns={"identifier"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\NodeDataStorageRepository")
 */
class NodeDataStorage extends AbstractEntity
{
    use IdentifierTrait;

    /**
     * @ORM\Column(name="level", type="string", length=20, nullable=false)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $level;

    /**
     * @ORM\Column(name="parser", type="string", length=20, nullable=false)
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $parser;

    /**
     * @ORM\Column(name="images_no", type="integer")
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $imagesNo = 0;

    /**
     * @ORM\Column(name="comments_no", type="integer")
     * @EntityVariable(convertable=true, writable=true, readable=true)
     */
    protected $commentsNo = 0;

    public function __construct()
    {
        if (!$this->getId()) {
            $this->createdAt = new \DateTime();
        }

        $this->lastUpdatedAt = new \DateTime();
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

    public function getParser(): ?string
    {
        return $this->parser;
    }

    public function setParser(string $parser): self
    {
        $this->parser = $parser;

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

}
