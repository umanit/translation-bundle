<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Utils\AnnotationHelper;

/**
 * Translation handler for @Doctrine\ORM\Mapping\Embeddable()
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class EmbeddedHandler implements TranslationHandlerInterface
{
    /**
     * @var AnnotationHelper
     */
    private $annotationHelper;

    /**
     * @var DoctrineObjectHandler
     */
    private $objectHandler;

    /**
     * EmbeddedHandler constructor.
     *
     * @param AnnotationHelper      $annotationHelper
     * @param DoctrineObjectHandler $objectHandler
     */
    public function __construct(AnnotationHelper $annotationHelper, DoctrineObjectHandler $objectHandler)
    {
        $this->annotationHelper = $annotationHelper;
        $this->objectHandler    = $objectHandler;
    }

    public function supports(TranslationArgs $args): bool
    {
        return null !== $args->getProperty() && $this->annotationHelper->isEmbedded($args->getProperty());
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
