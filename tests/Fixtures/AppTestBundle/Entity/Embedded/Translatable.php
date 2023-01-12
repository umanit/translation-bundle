<?php

namespace AppTestBundle\Entity\Embedded;

use Doctrine\ORM\Mapping as ORM;
use Umanit\TranslationBundle\Doctrine\Attribute\EmptyOnTranslate;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableTrait;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 * @ORM\Entity()
 */
class Translatable implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id;

    /** @ORM\Embedded(class="Address") */
    private $address;

    /**
     * @ORM\Embedded(class="Address")
     * @EmptyOnTranslate()
     */
    private $emptyAddress;

    public function __construct()
    {
        $this->address      = new Address();
        $this->emptyAddress = new Address();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     *
     * @return $this
     */
    public function setAddress($address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmptyAddress()
    {
        return $this->emptyAddress;
    }

    /**
     * @param mixed $emptyAddress
     *
     * @return $this
     */
    public function setEmptyAddress($emptyAddress = null)
    {
        $this->emptyAddress = $emptyAddress;

        return $this;
    }
}
