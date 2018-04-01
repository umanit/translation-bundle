<?php

namespace Umanit\TranslationBundle\Doctrine\Model;

interface TranslatableInterface
{
    /**
     * Returns "original" identifier (same for all translations of an entity)
     *
     * @return int
     */
    public function getOid();

    /**
     * Set the "original" identifier (same for all translations of en entity)
     *
     * @param int $oid
     */
    public function setOid($oid);

    /**
     * Returns entity's locale (fr/en/...)
     */
    public function getLocale();

    /**
     * Set entity's locale (fr/en/...)
     *
     * @param string $locale
     */
    public function setLocale($locale);

    /**
     * Set entity's identifier
     *
     * @param int $id
     */
    public function setId($id);

    /**
     * Returns entity's identifier
     *
     * @return int
     */
    public function getId();

    /**
     * Returns translations ids per locale
     *
     * @return array
     */
    public function getTranslations();

    /**
     * Set translations ids per locale
     *
     * @param array $translations
     */
    public function setTranslations(array $translations);
}
