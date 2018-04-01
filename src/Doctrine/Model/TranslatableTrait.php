<?php

namespace Umanit\TranslationBundle\Doctrine\Model;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait TranslatableTrait
{
    /**
     * @var UuidInterface
     * @ORM\Id()
     * @ORM\Column(name="uuid", type="string", length=36)
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Id()
     * @ORM\Column(name="locale", type="string", length=7)
     */
    protected $locale;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    protected $translations;

    /**
     * TranslatableTrait constructor.
     *
     * @param string             $locale
     * @param UuidInterface|null $uuid
     */
    public function __construct(string $locale, UuidInterface $uuid = null)
    {
        if (null === $uuid) {
            $uuid = Uuid::uuid4();
        }

        $this->locale = $locale;
        $this->uuid   = $uuid;
    }

    /**
     * Set the locale
     *
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
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
    public function setTranslations(array $translations): self
    {
        $this->translations = $translations;

        return $this;
    }
}
