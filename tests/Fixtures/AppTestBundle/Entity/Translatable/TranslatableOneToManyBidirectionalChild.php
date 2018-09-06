<?php

namespace AppTestBundle\Entity\Translatable;

use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;

/**
 * @ORM\Entity
 * @ORM\Table()
 */
class TranslatableOneToManyBidirectionalChild implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * Many Children have one Parent.
     *
     * @ORM\ManyToOne(
     *     targetEntity="AppTestBundle\Entity\Translatable\TranslatableOneToManyBidirectionalParent",
     *     inversedBy="children",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    private $parent;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     *
     * @return self
     */
    public function setParent($parent = null): self
    {
        $this->parent = $parent;

        return $this;
    }

}
