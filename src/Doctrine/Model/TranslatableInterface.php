<?php

namespace Umanit\TranslationBundle\Doctrine\Model;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
interface TranslatableInterface
{
    /**
     * Returns entity's locale (fr/en/...)
     */
    public function getLocale();

    /**
     * Set entity's locale (fr/en/...)
     *
     * @param string $locale
     */
    public function setLocale(string $locale = null);

    /**
     * Set entity's Translation UUID
     *
     * @param string $tuuid
     */
    public function setTuuid(string $tuuid);

    /**
     * Returns entity's Translation UUID
     */
    public function getTuuid();

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
    public function setTranslations(array $translations): TranslatableInterface;
}
