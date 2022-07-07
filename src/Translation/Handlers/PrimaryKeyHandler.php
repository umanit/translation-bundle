<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Utils\AttributeHelper;

/**
 * Handles translation of primary keys.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class PrimaryKeyHandler implements TranslationHandlerInterface
{
    /**
     * @var AttributeHelper
     */
    private $annotationHelper;

    public function __construct(AttributeHelper $annotationHelper)
    {
        $this->annotationHelper = $annotationHelper;
    }

    public function supports(TranslationArgs $args): bool
    {
        return null !== $args->getProperty() && $this->annotationHelper->isId($args->getProperty());
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
