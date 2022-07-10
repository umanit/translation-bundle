<?php

namespace AppTestBundle\Entity\Scalar;

use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Attribute\EmptyOnTranslate;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;

/**
 * @ORM\Entity
 * @ORM\Table()
 */
class CanNotBeNull implements TranslatableInterface
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
     * @EmptyOnTranslate()
     * @ORM\Column(type="string")
     */
    protected $empty_not_nullable;

    /**
     * @param string $empty_not_nullable
     *
     * @return CanNotBeNull
     */
    public function setEmptyNotNullable(string $empty_not_nullable = null): CanNotBeNull
    {
        $this->empty_not_nullable = $empty_not_nullable;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmptyNotNullable(): string
    {
        return $this->empty_not_nullable;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
