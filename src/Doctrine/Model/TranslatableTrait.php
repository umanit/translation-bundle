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
     * @ORM\Column(name="uuid", type="guid", length=36)
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Column(name="locale", type="string", length=7)
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
     * Set the UUID
     *
     * @param string $uuid
     *
     * @return $this
     */
    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Returns entity's UUID.
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
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
