<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait ParserTrait
{
    /**
     * @ORM\Column(name="parser", type="string", length=20, nullable=true)
     * @Groups("basic_data")
     */
    protected $parser;

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
    public function setParser($parser)
    {
        $this->parser = $parser;

        return $this;
    }
}
