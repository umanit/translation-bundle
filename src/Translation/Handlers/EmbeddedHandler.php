<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Utils\AttributeHelper;

/**
 * Translation handler for @Doctrine\ORM\Mapping\Embeddable()
 */
class EmbeddedHandler implements TranslationHandlerInterface
{
    private AttributeHelper $attributeHelper;
    private DoctrineObjectHandler $objectHandler;

    public function __construct(AttributeHelper $attributeHelper, DoctrineObjectHandler $objectHandler)
    {
        $this->attributeHelper = $attributeHelper;
        $this->objectHandler = $objectHandler;
    }

    public function supports(TranslationArgs $args): bool
    {
        return null !== $args->getProperty() && $this->attributeHelper->isEmbedded($args->getProperty());
    }

    public function handleSharedAmongstTranslations(TranslationArgs $args)
    {
        return $this->objectHandler->handleSharedAmongstTranslations($args);
    }

    public function handleEmptyOnTranslate(TranslationArgs $args)
    {
        return $this->objectHandler->handleEmptyOnTranslate($args);
    }

    public function translate(TranslationArgs $args)
    {
        return clone $args->getDataToBeTranslated();
    }
}
