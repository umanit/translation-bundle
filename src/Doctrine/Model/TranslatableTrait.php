<?php

namespace Umanit\TranslationBundle\Doctrine\Model;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
trait TranslatableTrait
{
    /**
     * @var UuidInterface
     * @ORM\Column(type="guid", length=36)
     */
    protected $tuuid;

    /**
     * @var string
     * @ORM\Column(type="string", length=7)
     */
    protected $locale;

    /**
     * @var array
     * @ORM\Column(type="json_array")
     */
    protected $translations = [];

    /**
     * Set the locale
     *
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale(string $locale = null): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Returns entity's locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set the Translation UUID
     *
     * @param string $tuuid
     *
     * @return $this
     */
    public function setTuuid(string $tuuid): self
    {
        $this->tuuid = $tuuid;

        return $this;
    }

    /**
     * Returns entity's Translation UUID.
     *
     * @return string
     */
    public function getTuuid()
    {
        return $this->tuuid;
    }

    /**
     * @return array
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    /**
     * @param array $translations
     *
     * @return $this
     */
    public function setTranslations(array $translations): TranslatableInterface
    {
        $this->translations = $translations;

        return $this;
    }
}
