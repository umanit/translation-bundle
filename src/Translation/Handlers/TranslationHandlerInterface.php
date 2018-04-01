<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
interface TranslationHandlerInterface
{
    /**
     * Defines if the handler supports the data to be translated.
     *
     * @param mixed $data
     *
     * @return bool
     */
    public function supports($data): bool;

    /**
     * Handles a SharedAmongstTranslations data translation.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function handleSharedAmongstTranslations($data);

    /**
     * Handles an EmptyOnTranslate data translation.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function handleEmptyOnTranslate($data);

    /**
     * Handles translation.
     *
     * @param mixed  $data
     *
     * @param string $locale
     *
     * @return mixed
     */
    public function translate($data, string $locale);
}