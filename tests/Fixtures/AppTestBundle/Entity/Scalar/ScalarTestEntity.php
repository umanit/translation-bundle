<?php

namespace AppTestBundle\Entity\Scalar;

use Doctrine\ORM\Mapping as ORM;
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
}
