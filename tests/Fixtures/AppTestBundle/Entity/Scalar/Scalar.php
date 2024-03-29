<?php

namespace AppTestBundle\Entity\Scalar;

use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Attribute\EmptyOnTranslate;
use Umanit\TranslationBundle\Doctrine\Attribute\SharedAmongstTranslations;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;

/**
 * @ORM\Entity
 * @ORM\Table()
 */
class Scalar implements TranslatableInterface
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
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * Scalar value.
     *
     * @var string
     * @SharedAmongstTranslations()
     * @ORM\Column(type="string", nullable=true)
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
     * @return Scalar
     */
    public function setTitle(string $title = null)
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
     * @return Scalar
     */
    public function setShared(string $shared = null)
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
     * @return Scalar
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

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
