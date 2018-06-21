<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Umanit\TranslationBundle\Translation\Args\TranslationArgs;

/**
 * Handles scalar type translation.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class ScalarHandler implements TranslationHandlerInterface
{
    public function supports(TranslationArgs $args): bool
    {
        $data = $args->getDataToBeTranslated();

        return (!\is_object($data) || $data instanceof \DateTime);
    }

    public function handleSharedAmongstTranslations(TranslationArgs $args)
    {
        return $args->getDataToBeTranslated();
    }

    public function handleEmptyOnTranslate(TranslationArgs $args)
    {
        return null;
    }

    public function translate(TranslationArgs $args)
    {
        return $args->getDataToBeTranslated();
    }
}
