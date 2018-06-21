<?php

namespace AppTestBundle\Entity\Translatable;

use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Annotation\EmptyOnTranslate;
use Umanit\TranslationBundle\Doctrine\Annotation\SharedAmongstTranslations;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;

/**
 * @ORM\Entity
 * @ORM\Table()
 */
class TranslatableOneToOneBidirectionalParent implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * @var mixed
     *
     * @ORM\OneToOne(targetEntity="AppTestBundle\Entity\Translatable\TranslatableOneToOneBidirectionalChild", mappedBy="simpleParent", cascade={"all"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $simpleChild;

    /**
     * @var mixed
     *
     * @ORM\OneToOne(targetEntity="AppTestBundle\Entity\Translatable\TranslatableOneToOneBidirectionalChild", mappedBy="sharedParent")
     * @ORM\JoinColumn(nullable=true)
     * @SharedAmongstTranslations()
     */
    private $sharedChild;

    /**
     * @var mixed
     *
     * @ORM\OneToOne(targetEntity="AppTestBundle\Entity\Translatable\TranslatableOneToOneBidirectionalChild", mappedBy="emptyParent")
     * @ORM\JoinColumn(nullable=true)
     * @EmptyOnTranslate()
     */
    private $emptyChild;

    /**
     * @return mixed
     */
    public function getSimpleChild()
    {
        return $this->simpleChild;
    }

    /**
     * @param mixed $simpleChild
     *
     * @return $this
     */
    public function setSimpleChild($simpleChild)
    {
        $this->simpleChild = $simpleChild;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSharedChild()
    {
        return $this->sharedChild;
    }

    /**
     * @param mixed $sharedChild
     *
     * @return $this
     */
    public function setSharedChild($sharedChild)
    {
        $this->sharedChild = $sharedChild;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmptyChild()
    {
        return $this->emptyChild;
    }

    /**
     * @param mixed $emptyChild
     *
     * @return $this
     */
    public function setEmptyChild($emptyChild)
    {
        $this->emptyChild = $emptyChild;

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
