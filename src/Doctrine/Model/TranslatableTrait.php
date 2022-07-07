<?php

namespace Umanit\TranslationBundle\Doctrine\Model;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
trait TranslatableTrait
{
    #[ORM\Column(type: 'guid', length: 36)]
    protected ?string $tuuid;

    #[ORM\Column(type: 'string', length: 7)]
    protected ?string $locale;

    #[ORM\Column(type: 'json')]
    protected array $translations = [];

    /**
     * Set the locale
     */
    public function setLocale(string $locale = null): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Returns entity's locale.
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set the Translation UUID
     */
    public function setTuuid(?string $tuuid): self
    {
        $this->tuuid = $tuuid;

        return $this;
    }

    /**
     * Returns entity's Translation UUID.
     */
    public function getTuuid(): ?string
    {
        return $this->tuuid;
    }

    public function setTranslations(array $translations): TranslatableInterface
    {
        $this->translations = $translations;

        return $this;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }
}
