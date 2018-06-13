<?php

namespace AppTestBundle\Entity\Translatable;

use AppTestBundle\Entity\Scalar\Scalar;
use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Annotation\EmptyOnTranslate;
use Umanit\TranslationBundle\Doctrine\Annotation\SharedAmongstTranslations;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;

/**
 * @ORM\Entity
 * @ORM\Table()
 */
class TranslatableOneToOneUnidirectional implements TranslatableInterface
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
     * @var Scalar
     * @ORM\OneToOne(targetEntity="AppTestBundle\Entity\Scalar\Scalar", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $simple;

    /**
     * Scalar value.
     *
     * @var Scalar
     * @SharedAmongstTranslations()
     * @ORM\OneToOne(targetEntity="AppTestBundle\Entity\Scalar\Scalar", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $shared;

    /**
     * Scalar value.
     *
     * @var Scalar
     * @EmptyOnTranslate()
     * @ORM\OneToOne(targetEntity="AppTestBundle\Entity\Scalar\Scalar", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $empty;

    /**
     * @return Scalar
     */
    public function getSimple()
    {
        return $this->simple;
    }

    /**
     * @param Scalar $simple
     *
     * @return $this
     */
    public function setSimple(Scalar $simple)
    {
        $this->simple = $simple;

        return $this;
    }

    /**
     * @return Scalar
     */
    public function getShared()
    {
        return $this->shared;
    }

    /**
     * @param Scalar $shared
     *
     * @return $this
     */
    public function setShared(Scalar $shared)
    {
        $this->shared = $shared;

        return $this;
    }

    /**
     * @return Scalar
     */
    public function getEmpty()
    {
        return $this->empty;
    }

    /**
     * @param Scalar $empty
     *
     * @return $this
     */
    public function setEmpty(Scalar $empty = null)
    {
        $this->empty = $empty;

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
