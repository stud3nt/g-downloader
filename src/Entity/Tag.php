<?php

namespace App\Entity;

use App\Annotation\EntityVariable;
use App\Annotation\Serializer\ObjectVariable;
use App\Entity\Base\AbstractEntity;
use App\Entity\Traits\{
    CreatedAtTrait
};
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table("tags")
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 */
class Tag extends AbstractEntity
{
    use CreatedAtTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", length=8, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ObjectVariable(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=28, nullable=false)
     * @ObjectVariable(type="string")
     */
    protected ?string $name = null;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function __construct()
    {
        if (!$this->id) {
            $this->createdAt = new \DateTime();
        }
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
