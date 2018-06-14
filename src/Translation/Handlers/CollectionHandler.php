<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Translation\EntityTranslator;
use Umanit\TranslationBundle\Utils\AnnotationHelper;

/**
 * Collection handler, used for ManyToMany bidirectional association.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class CollectionHandler implements TranslationHandlerInterface
{
    /**
     * @var AnnotationHelper
     */
    private $annotationHelper;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var Reader
     */
    private $reader;
    /**
     * @var EntityTranslator
     */
    private $translator;

    public function __construct(
        AnnotationHelper $annotationHelper,
        EntityManagerInterface $em,
        Reader $reader,
        EntityTranslator $translator
    ) {
        $this->annotationHelper = $annotationHelper;
        $this->em               = $em;
        $this->reader           = $reader;
        $this->translator       = $translator;
    }

    public function supports(TranslationArgs $args): bool
    {
        if (!$args->getDataToBeTranslated() instanceof Collection) {
            return false;
        }

        $propAnnotations = $this->reader->getPropertyAnnotations($args->getProperty());
        foreach ($propAnnotations as $propAnnotation) {
            if (property_exists($propAnnotation, 'mappedBy') && null !== $propAnnotation->mappedBy) {
                return true;
            }
        }

        return false;
    }

    public function handleSharedAmongstTranslations(TranslationArgs $args)
    {
        // @todo handle SharedAmongstTranslations
        return null;
    }

    public function handleEmptyOnTranslate(TranslationArgs $args)
    {
        // @todo handle EmptyOnTranslate
        return null;
    }

    public function translate(TranslationArgs $args)
    {
        /** @var Collection $collection */
        $collection    = $args->getDataToBeTranslated();
        $newCollection = clone $collection;
        $newOwner      = $args->getTranslatedParent();
        // Get the owner's "mappedBy"
        $associations = $this->em->getClassMetadata(\get_class($newOwner))->getAssociationMappings();
        $association  = reset($associations);
        $mappedBy     = $association['mappedBy'];

        // Iterate through collection and set
        // their owner owner to $newOwner
        foreach ($newCollection as $key => $item) {
            $reflection = new \ReflectionProperty(\get_class($item), $mappedBy);
            $reflection->setAccessible(true);
            // Set item's owner to null
            $reflection->setValue($item, new ArrayCollection([]));
            // Translate the item
            $itemTrans  = $this->translator->translate($item, $args->getTargetLocale());
            // Set the translated item new owner
            $reflection->setValue($itemTrans, new ArrayCollection([$newOwner]));
            $newCollection[$key] = $itemTrans;
        }

        return $newCollection;
    }

}
