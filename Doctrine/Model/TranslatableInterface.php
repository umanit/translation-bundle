<?php

namespace Umanit\TranslationBundle\Doctrine\Model;

interface TranslatableInterface
{
    /**
     * @return int
     */
    public function getOid();

    /**
     * @param int $oid
     */
    public function setOid($oid);

    public function getLocale();

    public function setLocale($locale);

    public function setId($id);

    public function getTranslations();

    public function setTranslations(array $translations);
}
