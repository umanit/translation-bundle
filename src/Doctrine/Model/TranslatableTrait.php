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
     * @var string|null
     * @ORM\Column(type="guid", length=36)
     */
    #[ORM\Column(type: 'guid', length: 36)]
    protected ?string $tuuid = null;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=7)
     */
    #[ORM\Column(type: 'string', length: 7)]
    protected ?string $locale = null;

    /**
     * @var array
     * @ORM\Column(type="json")
     */
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
    public function getLocale(): ?string
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

    public function setTranslations(array $translations): self
    {
        $this->translations = $translations;

        return $this;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }
}
