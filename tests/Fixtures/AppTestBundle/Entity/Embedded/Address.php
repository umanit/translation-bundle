<?php

namespace AppTestBundle\Entity\Embedded;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 *
 * @ORM\Embeddable()
 */
class Address
{
    /** @ORM\Column(type="string", nullable=true) */
    private $street;

    /** @ORM\Column(type="string", nullable=true) */
    private $postalCode;

    /** @ORM\Column(type="string", nullable=true) */
    private $city;

    /** @ORM\Column(type="string", nullable=true) */
    private $country;

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param mixed $street
     *
     * @return $this
     */
    public function setStreet($street = null)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param mixed $postalCode
     *
     * @return $this
     */
    public function setPostalCode($postalCode = null)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     *
     * @return $this
     */
    public function setCity($city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     *
     * @return $this
     */
    public function setCountry($country = null)
    {
        $this->country = $country;

        return $this;
    }
}
