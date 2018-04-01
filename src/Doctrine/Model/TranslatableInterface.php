<?php

namespace Umanit\TranslationBundle\Doctrine\Model;

interface TranslatableInterface
{
    /**
     * Returns entity's locale (fr/en/...)
     */
    public function getLocale(): string;

    /**
     * Set entity's locale (fr/en/...)
     *
     * @param string $locale
     */
    public function setLocale(string $locale);

    /**
     * Returns translations ids per locale
     *
     * @return array
     */
    public function getTranslations(): array;

    /**
     * Set translations ids per locale
     *
     * @param array $translations
     *
     * @return TranslatableInterface
     */
    public function setTranslations(array $translations): self;
}
