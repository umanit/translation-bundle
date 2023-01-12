<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Utils\AttributeHelper;

/**
 * Handles translation of primary keys.
 */
class PrimaryKeyHandler implements TranslationHandlerInterface
{
    private AttributeHelper $attributeHelper;

    public function __construct(AttributeHelper $attributeHelper)
    {
        $this->attributeHelper = $attributeHelper;
    }

    public function supports(TranslationArgs $args): bool
    {
        return null !== $args->getProperty() && $this->attributeHelper->isId($args->getProperty());
    }

    public function handleSharedAmongstTranslations(TranslationArgs $args)
    {
        return null;
    }

    public function handleEmptyOnTranslate(TranslationArgs $args)
    {
        return null;
    }

    public function translate(TranslationArgs $args)
    {
        return null;
    }
}
