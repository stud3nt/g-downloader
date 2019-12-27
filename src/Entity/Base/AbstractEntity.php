<?php

namespace App\Entity\Base;

use App\Annotation\EntityVariable;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

abstract class AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", length=8, nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @EntityVariable(convertable=true, writable=false, readable=true, inAllConvertNames=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getClass()
    {
        $pos = strpos(get_called_class(), 'App');

        if ($pos > 0)
            return substr(get_called_class(), $pos);

        return get_called_class();
    }

    public function getClassName()
    {
        $arr = explode('\\', $this->getClass());

        return end($arr);
    }

    public function getEntity()
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $this->getClassName())), '_');
    }

    public static function getArrayKeys($projectionType = null, $reservation = null, User $user = null)
    {
        return [];
    }
}