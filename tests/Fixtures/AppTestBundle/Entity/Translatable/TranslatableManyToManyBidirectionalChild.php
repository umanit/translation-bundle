<?php

namespace AppTestBundle\Entity\Translatable;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;

/**
 * @ORM\Entity
 */
class TranslatableManyToManyBidirectionalChild implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * Scalar value.
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="AppTestBundle\Entity\Translatable\TranslatableManyToManyBidirectionalParent",
     *     cascade={"persist"},
     *     inversedBy="simpleChildren"
     * )
     */
    protected $simpleParents;

    public function __construct()
    {
        $this->simpleParents = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection
     */
    public function getSimpleParents()
    {
        return $this->simpleParents;
    }

    public function addSimpleParent(TranslatableManyToManyBidirectionalParent $parent)
    {
        $this->simpleParents[] = $parent;

        return $this;
    }
}
