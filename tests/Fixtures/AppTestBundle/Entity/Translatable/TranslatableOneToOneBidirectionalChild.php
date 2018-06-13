<?php

namespace AppTestBundle\Entity\Translatable;

use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;

/**
 * @ORM\Entity
 * @ORM\Table()
 */
class TranslatableOneToOneBidirectionalChild implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="AppTestBundle\Entity\Translatable\TranslatableOneToOneBidirectionalParent", inversedBy="simpleChild")
     * @ORM\JoinColumn(nullable=true)
     */
    private $simpleParent;

    /**
     * @ORM\OneToOne(targetEntity="AppTestBundle\Entity\Translatable\TranslatableOneToOneBidirectionalParent", inversedBy="sharedChild")
     * @ORM\JoinColumn(nullable=true)
     */
    private $sharedParent;

    /**
     * @ORM\OneToOne(targetEntity="AppTestBundle\Entity\Translatable\TranslatableOneToOneBidirectionalParent", inversedBy="emptyChild")
     * @ORM\JoinColumn(nullable=true)
     */
    private $emptyParent;

    /**
     * @return mixed
     */
    public function getSimpleParent()
    {
        return $this->simpleParent;
    }

    /**
     * @param mixed $simpleParent
     *
     * @return $this
     */
    public function setSimpleParent($simpleParent)
    {
        $this->simpleParent = $simpleParent;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSharedParent()
    {
        return $this->sharedParent;
    }

    /**
     * @param mixed $sharedParent
     *
     * @return $this
     */
    public function setSharedParent($sharedParent)
    {
        $this->sharedParent = $sharedParent;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmptyParent()
    {
        return $this->emptyParent;
    }

    /**
     * @param mixed $emptyParent
     *
     * @return $this
     */
    public function setEmptyParent($emptyParent)
    {
        $this->emptyParent = $emptyParent;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

}
