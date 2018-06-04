<?php

namespace AppTestBundle\Entity\Translatable;

use AppTestBundle\Entity\Scalar\ScalarTestEntity;
use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Annotation\EmptyOnTranslate;
use Umanit\TranslationBundle\Doctrine\Annotation\SharedAmongstTranslations;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;

/**
 * @ORM\Entity
 * @ORM\Table()
 */
class TranslatableManyToOneEntity implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * Scalar value.
     *
     * @var ScalarTestEntity
     * @ORM\ManyToOne(targetEntity="\AppTestBundle\Entity\Scalar\ScalarTestEntity", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $simple;

    /**
     * Scalar value.
     *
     * @var ScalarTestEntity
     * @SharedAmongstTranslations()
     * @ORM\ManyToOne(targetEntity="\AppTestBundle\Entity\Scalar\ScalarTestEntity", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $shared;

    /**
     * Scalar value.
     *
     * @var ScalarTestEntity
     * @EmptyOnTranslate()
     * @ORM\ManyToOne(targetEntity="\AppTestBundle\Entity\Scalar\ScalarTestEntity", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    protected $empty;

    /**
     * @return ScalarTestEntity
     */
    public function getSimple()
    {
        return $this->simple;
    }

    /**
     * @param ScalarTestEntity $simple
     *
     * @return $this
     */
    public function setSimple(ScalarTestEntity $simple)
    {
        $this->simple = $simple;

        return $this;
    }

    /**
     * @return ScalarTestEntity
     */
    public function getShared()
    {
        return $this->shared;
    }

    /**
     * @param ScalarTestEntity $shared
     *
     * @return $this
     */
    public function setShared(ScalarTestEntity $shared)
    {
        $this->shared = $shared;

        return $this;
    }

    /**
     * @return ScalarTestEntity
     */
    public function getEmpty()
    {
        return $this->empty;
    }

    /**
     * @param ScalarTestEntity $empty
     *
     * @return $this
     */
    public function setEmpty(ScalarTestEntity $empty)
    {
        $this->empty = $empty;

        return $this;
    }
}
