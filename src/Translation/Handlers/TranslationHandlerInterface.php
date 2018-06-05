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
     * @param mixed                    $data
     * @param \ReflectionProperty|null $property
     *
     * @return bool
     */
    public function supports($data, \ReflectionProperty $property = null): bool;

    /**
     * Handles a SharedAmongstTranslations data translation.
     *
     * @param mixed  $data
     * @param string $locale
     *
     * @return mixed
     */
    public function handleSharedAmongstTranslations($data, string $locale);

    /**
     * Handles an EmptyOnTranslate data translation.
     *
     * @param mixed  $data
     * @param string $locale
     *
     * @return mixed
     */
    public function handleEmptyOnTranslate($data, string $locale);

    /**
     * Handles translation.
     *
     * @param mixed                    $data
     * @param string                   $locale
     * @param \ReflectionProperty|null $property
     * @param mixed|null                     $parent
     *
     * @return mixed
     */
    public function translate($data, string $locale, \ReflectionProperty $property = null, $parent = null);
}
