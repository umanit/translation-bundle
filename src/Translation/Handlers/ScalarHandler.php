<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

/**
 * Handles scalar type translation.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class ScalarHandler implements TranslationHandlerInterface
{
    public function supports($data, \ReflectionProperty $property = null): bool
    {
        return (!\is_object($data) || $data instanceof \DateTime);
    }

    public function handleSharedAmongstTranslations($data, string $locale)
    {
        return $data;
    }

    public function handleEmptyOnTranslate($data, string $locale)
    {
        return null;
    }

    public function translate($data, string $locale, \ReflectionProperty $property = null, $parent = null)
    {
        return $data;
    }
}
