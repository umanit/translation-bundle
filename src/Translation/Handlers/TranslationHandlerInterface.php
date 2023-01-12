<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Umanit\TranslationBundle\Translation\Args\TranslationArgs;

interface TranslationHandlerInterface
{
    /**
     * Defines if the handler supports the data to be translated.
     *
     * @param TranslationArgs $args
     *
     * @return bool
     */
    public function supports(TranslationArgs $args): bool;

    /**
     * Handles a SharedAmongstTranslations data translation.
     *
     * @param TranslationArgs $args
     *
     * @return mixed
     */
    public function handleSharedAmongstTranslations(TranslationArgs $args);

    /**
     * Handles an EmptyOnTranslate data translation.
     *
     * @param TranslationArgs $args
     *
     * @return mixed
     */
    public function handleEmptyOnTranslate(TranslationArgs $args);

    /**
     * Handles translation.
     *
     * @param TranslationArgs $args
     *
     * @return mixed
     */
    public function translate(TranslationArgs $args);
}
