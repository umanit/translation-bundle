<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

/**
 * Handles scalar type translation.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class Scalar implements TranslationHandlerInterface
{
    public function supports($data): bool
    {
        return (!is_object($data) || $data instanceof \DateTime);
    }

    public function handleSharedAmongstTranslations($data)
    {
        return $data;
    }

    public function handleEmptyOnTranslate($data)
    {
        return null;
    }

    public function translate($data, string $locale)
    {
        return $data;
    }
}
