<?php

namespace AppTestBundle\Entity\Translatable;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ManyToManyBidirectionalChild
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="AppTestBundle\Entity\Translatable\TranslatableManyToManyBidirectionalParent",
     *     cascade={"persist"},
     *     inversedBy="sharedChildren"
     * )
     * @ORM\JoinTable(name="shared_translatablemanytomanybidirectionalchild_translatablemanytomanybidirectionalparent")
     */
    protected $sharedParents;

    public function __construct()
    {
        $this->sharedParents = new ArrayCollection();
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
    public function getSharedParents()
    {
        return $this->sharedParents;
    }

    public function addSharedParent(TranslatableManyToManyBidirectionalParent $parent)
    {
        $this->sharedParents[] = $parent;

        return $this;
    }
}
