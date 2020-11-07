<?php

namespace App\Entity;

use App\Annotation\Serializer\ObjectVariable;
use App\Entity\Base\AbstractEntity;
use App\Utils\StringHelper;
use App\Entity\Traits\{CreatedAtTrait, DescriptionTrait};
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table("categories")
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Category extends AbstractEntity
{
    use CreatedAtTrait,
        DescriptionTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", length=8, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ObjectVariable(type="integer", writable=false)
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     * @ObjectVariable(type="string")
     */
    protected ?string $name = null;

    /**
     * @ORM\Column(name="label", type="string", length=100, nullable=true)
     * @ObjectVariable(type="string")
     */
    protected ?string $label = null;

    /**
     * @ORM\Column(name="symbol", type="string", length=100, nullable=false)
     * @ObjectVariable(type="string")
     */
    protected ?string $symbol = null;

    /**
     * @ORM\Column(name="active", type="boolean", length=1, options={"unsigned"=true, "default":0})
     * @ObjectVariable(type="boolean")
     */
    protected bool $active = false;

    public function __construct()
    {
        if (!$this->id) {
            $this->createdAt = new \DateTime();
        }
    }

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     * @return Category;
     */
    public function setLabel($label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * @param mixed $symbol
     * @return Category;
     */
    public function setSymbol($symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @ORM\PrePersist()
     * @return Category
     */
    public function completeSymbolFromName(): self
    {
        if (empty($this->getSymbol))
            $this->setSymbol(StringHelper::basicCharactersOnly($this->getName()));

        return $this;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     * @return Category;
     */
    public function setActive($active): self
    {
        $this->active = $active;

        return $this;
    }
}
