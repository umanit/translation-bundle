<?php

namespace AppTestBundle\Entity\Translatable;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;

/**
 * @ORM\Entity
 * @ORM\Table()
 */
class TranslatableOneToManyBidirectionalParent implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * A Child has one Parent.
     *
     * @var ArrayCollection
     * @ORM\OneToMany(
     *     targetEntity="AppTestBundle\Entity\Translatable\TranslatableOneToManyBidirectionalChild",
     *     cascade={"persist"},
     *     mappedBy="parent"
     * )
     */
    protected $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
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
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Collection $children
     *
     * @return self
     */
    public function setChildren(Collection $children = null): self
    {
        $this->children = $children;

        foreach ($children as $child) {
            $child->setParent($this);
        }

        return $this;
    }

}
