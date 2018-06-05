<?php

namespace Umanit\TranslationBundle\Translation\Args;

/**
 * Translation args DTO.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class TranslationArgs
{
    /** @var mixed */
    protected $dataToBeTranslated;

    /** @var string */
    protected $sourceLocale;

    /** @var string */
    protected $targetLocale;

    /** @var mixed */
    protected $translatedParent;

    /** @var \ReflectionProperty */
    protected $property;

    /**
     * TranslationArgs constructor.
     *
     * @param mixed  $dataToBeTranslated
     * @param string $sourceLocale
     * @param string $targetLocale
     */
    public function __construct($dataToBeTranslated, $sourceLocale, $targetLocale)
    {
        $this->dataToBeTranslated = $dataToBeTranslated;
        $this->sourceLocale       = $sourceLocale;
        $this->targetLocale       = $targetLocale;
    }

    /**
     * Returns the source data that will be translated.
     *
     * @return mixed
     */
    public function getDataToBeTranslated()
    {
        return $this->dataToBeTranslated;
    }

    /**
     * Sets the source data that will be translated.
     *
     * @param mixed $dataToBeTranslated
     *
     * @return $this
     */
    public function setDataToBeTranslated($dataToBeTranslated)
    {
        $this->dataToBeTranslated = $dataToBeTranslated;

        return $this;
    }

    /**
     * Returns the locale of the original data.
     *
     * @return string
     */
    public function getSourceLocale()
    {
        return $this->sourceLocale;
    }

    /**
     * Sets the locale of the original data.
     *
     * @param string $sourceLocale
     *
     * @return $this
     */
    public function setSourceLocale(string $sourceLocale)
    {
        $this->sourceLocale = $sourceLocale;

        return $this;
    }

    /**
     * Returns the locale of the translated data.
     *
     * @return string
     */
    public function getTargetLocale()
    {
        return $this->targetLocale;
    }

    /**
     * Sets the locale of the translated data.
     *
     * @param string $targetLocale
     *
     * @return $this
     */
    public function setTargetLocale(string $targetLocale)
    {
        $this->targetLocale = $targetLocale;

        return $this;
    }

    /**
     * Returns the parent of the data translation.
     * Only sets when translating association.
     *
     * @return mixed
     */
    public function getTranslatedParent()
    {
        return $this->translatedParent;
    }

    /**
     * Sets the parent of the data translation.
     *
     * @param mixed $translatedParent
     *
     * @return $this
     */
    public function setTranslatedParent($translatedParent)
    {
        $this->translatedParent = $translatedParent;

        return $this;
    }

    /**
     * Returns the property associated to the translation.
     * Only sets when translating association.
     *
     * @return \ReflectionProperty
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Returns the property associated to the translation.
     *
     * @param \ReflectionProperty $property
     *
     * @return $this
     */
    public function setProperty(\ReflectionProperty $property)
    {
        $this->property = $property;

        return $this;
    }


}
