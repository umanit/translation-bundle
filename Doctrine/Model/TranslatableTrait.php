<?php

namespace Umanit\TranslationBundle\Doctrine\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\PropertyAccess\PropertyAccess;

trait TranslatableTrait
{
    /**
     * @var int
     *
     * @ORM\Column(name="oid", type="integer", nullable=true)
     */
    protected $oid;

    /**
     * @var string
     * @ORM\Column(name="locale", type="string", length=7)
     */
    protected $locale;

    /**
     * @var array
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $translations;

    /**
     * @return int
     */
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * @param int $oid
     */
    public function setOid($oid)
    {
        $this->oid = $oid;
    }

    /**
     * Set the locale
     *
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        // Set child locale
        $reflection = new \ReflectionClass(self::class);
        $accessor   = PropertyAccess::createPropertyAccessor();

        foreach ($reflection->getProperties() as $property) {
            $propValue = $accessor->getValue($this, $property->name);

            if ($propValue instanceof TranslatableInterface) {
                $propValue->setLocale($locale);
            }

            if ($propValue instanceof \iterable) {
                foreach ($propValue as $subProp) {
                    if ($subProp instanceof TranslatableInterface) {
                        $subProp->setLocale($locale);
                    }
                }
            }
        }

        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param null|int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return array
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param array $translations
     *
     * @return $this
     */
    public function setTranslations(array $translations)
    {
        $this->translations = $translations;

        return $this;
    }
}
