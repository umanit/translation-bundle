<?php

namespace Umanit\TranslationBundle\Translation\Args;

/**
 * Translation args DTO.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class TranslationArgs
{
    protected mixed $dataToBeTranslated;
    protected string $sourceLocale;
    protected string $targetLocale;
    protected mixed $translatedParent;
    protected ?\ReflectionProperty $property = null;

    public function __construct(mixed $dataToBeTranslated, string $sourceLocale, string $targetLocale)
    {
        $this->dataToBeTranslated = $dataToBeTranslated;
        $this->sourceLocale = $sourceLocale;
        $this->targetLocale = $targetLocale;
    }

    /**
     * Returns the source data that will be translated.
     */
    public function getDataToBeTranslated(): mixed
    {
        return $this->dataToBeTranslated;
    }

    /**
     * Sets the source data that will be translated.
     */
    public function setDataToBeTranslated(mixed $dataToBeTranslated): self
    {
        $this->dataToBeTranslated = $dataToBeTranslated;

        return $this;
    }

    /**
     * Returns the locale of the original data.
     */
    public function getSourceLocale(): string
    {
        return $this->sourceLocale;
    }

    /**
     * Sets the locale of the original data.
     */
    public function setSourceLocale(string $sourceLocale): self
    {
        $this->sourceLocale = $sourceLocale;

        return $this;
    }

    /**
     * Returns the locale of the translated data.
     */
    public function getTargetLocale(): string
    {
        return $this->targetLocale;
    }

    /**
     * Sets the locale of the translated data.
     */
    public function setTargetLocale(string $targetLocale): self
    {
        $this->targetLocale = $targetLocale;

        return $this;
    }

    /**
     * Returns the parent of the data translation.
     * Only sets when translating association.
     */
    public function getTranslatedParent(): mixed
    {
        return $this->translatedParent;
    }

    /**
     * Sets the parent of the data translation.
     */
    public function setTranslatedParent($translatedParent): self
    {
        $this->translatedParent = $translatedParent;

        return $this;
    }

    /**
     * Returns the property associated to the translation.
     * Only sets when translating association.
     */
    public function getProperty(): ?\ReflectionProperty
    {
        return $this->property;
    }

    /**
     * Returns the property associated to the translation.
     */
    public function setProperty(?\ReflectionProperty $property): self
    {
        $this->property = $property;

        return $this;
    }
}
