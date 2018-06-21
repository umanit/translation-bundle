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
     * Returns entity's Translation UUID
     */
    public function getTuuid();

    /**
     * Returns translations ids per locale
     *
     * @return array
     */
    public function getTranslations(): array;
}
