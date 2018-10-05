<?php

namespace AppTestBundle\Entity\Translatable;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Annotation\EmptyOnTranslate;
use Umanit\TranslationBundle\Doctrine\Annotation\SharedAmongstTranslations;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;

/**
 * @ORM\Entity
 */
class TranslatableManyToManyBidirectionalParent implements TranslatableInterface
{
    use TranslatableTrait;

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
     *     targetEntity="AppTestBundle\Entity\Translatable\TranslatableManyToManyBidirectionalChild",
     *     mappedBy="simpleParents"
     * )
     */
    private $simpleChildren;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="AppTestBundle\Entity\Translatable\TranslatableManyToManyBidirectionalChild",
     *     mappedBy="emptyParents"
     * )
     * @ORM\JoinTable(name="empty_translatablemanytomanybidirectionalchild_translatablemanytomanybidirectionalparent")
     * @EmptyOnTranslate()
     */
    private $emptyChildren;


    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(
     *     targetEntity="AppTestBundle\Entity\Translatable\ManyToManyBidirectionalChild",
     *     mappedBy="sharedParents",
     *     cascade={"persist"}
     * )
     * @ORM\JoinTable(name="shared_manytomanybidirectionalchild_translatablemanytomanybidirectionalparent")
     * @SharedAmongstTranslations()
     */
    private $sharedChildren;

    public function __construct()
    {
        $this->simpleChildren = new ArrayCollection();
        $this->emptyChildren  = new ArrayCollection();
        $this->sharedChildren = new ArrayCollection();
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
    public function getSimpleChildren()
    {
        return $this->simpleChildren;
    }

    public function addSimpleChild(TranslatableManyToManyBidirectionalChild $child)
    {
        $child->addSimpleParent($this);

        $this->simpleChildren[] = $child;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getEmptyChildren()
    {
        return $this->emptyChildren;
    }

    public function addEmptyChild(TranslatableManyToManyBidirectionalChild $child)
    {
        $child->addEmptyParent($this);

        $this->emptyChildren[] = $child;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSharedChildren()
    {
        return $this->sharedChildren;
    }

    /**
     * @param Collection $sharedChildren
     *
     * @return TranslatableManyToManyBidirectionalParent
     */
    public function setSharedChildren(Collection $sharedChildren)
    {
        $this->sharedChildren = $sharedChildren;

        return $this;
    }

    public function addSharedChild(ManyToManyBidirectionalChild $child)
    {
        $child->addSharedParent($this);

        $this->sharedChildren[] = $child;

        return $this;
    }
}
