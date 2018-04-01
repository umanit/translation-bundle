<?php

namespace AppTestBundle\Entity\Scalar;

use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Annotation\EmptyOnTranslate;
use Umanit\TranslationBundle\Doctrine\Annotation\SharedAmongstTranslations;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="scalar")
 */
class ScalarTestEntity implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * Scalar value.
     *
     * @var string
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * Scalar value.
     *
     * @var string
     * @SharedAmongstTranslations()
     * @ORM\Column(type="string")
     */
    protected $shared;

    /**
     * Scalar value.
     *
     * @var string
     * @EmptyOnTranslate()
     * @ORM\Column(type="string", nullable=true)
     */
    protected $empty;

    /**
     * @param string $title
     *
     * @return ScalarTestEntity
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $shared
     *
     * @return ScalarTestEntity
     */
    public function setShared(string $shared)
    {
        $this->shared = $shared;

        return $this;
    }

    /**
     * @return string
     */
    public function getShared()
    {
        return $this->shared;
    }

    /**
     * @param string $empty
     *
     * @return ScalarTestEntity
     */
    public function setEmpty(string $empty = null)
    {
        $this->empty = $empty;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmpty()
    {
        return $this->empty;
    }
}
