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
 * Handles translation of OneToMany relations.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class BidirectionalOneToManyHandler implements TranslationHandlerInterface
{
    /**
     * @var AnnotationHelper
     */
    private $annotationHelper;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var EntityTranslator
     */
    private $translator;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * BidirectionalManyToOneHandler constructor.
     *
     * @param AnnotationHelper       $annotationHelper
     * @param Reader                 $reader
     * @param EntityTranslator       $translator
     * @param EntityManagerInterface $em
     */
    public function __construct(
        AnnotationHelper $annotationHelper,
        Reader $reader,
        EntityTranslator $translator,
        EntityManagerInterface $em
    ) {
        $this->annotationHelper = $annotationHelper;
        $this->reader           = $reader;
        $this->translator       = $translator;
        $this->em               = $em;
    }

    public function supports(TranslationArgs $args): bool
    {
        if ($args->getProperty() && $this->annotationHelper->isOneToMany($args->getProperty())) {
            $propAnnotations = $this->reader->getPropertyAnnotations($args->getProperty());
            foreach ($propAnnotations as $propAnnotation) {
                if (property_exists($propAnnotation, 'mappedBy') && null !== $propAnnotation->mappedBy) {
                    return true;
                }
            }
        }

        return false;
    }

    public function handleSharedAmongstTranslations(TranslationArgs $args)
    {
        $data    = $args->getDataToBeTranslated();
        $message =
            '%class%::%prop% is a Bidirectional OneToMany, it cannot be shared '.
            'amongst translations. Either remove the @SharedAmongstTranslation '.
            'annotation or choose another association type.';

        throw new \ErrorException(strtr($message, [
            '%class%' => \get_class($data),
            '%prop%'  => $args->getProperty()->name,
        ]));
    }

    public function handleEmptyOnTranslate(TranslationArgs $args)
    {
        return new ArrayCollection();
    }

    public function translate(TranslationArgs $args)
    {
        /** @var Collection $collection */
        $collection    = $args->getDataToBeTranslated();
        $newCollection = clone $collection;
        $newOwner      = $args->getTranslatedParent();
        // Get the owner's "mappedBy"
        $associations = $this->em->getClassMetadata(\get_class($newOwner))->getAssociationMappings();
        $association  = $associations[$args->getProperty()->name];
        $mappedBy     = $association['mappedBy'];

        // Iterate through collection and set
        // their owner owner to $newOwner
        foreach ($newCollection as $key => $item) {
            $reflection = new \ReflectionProperty(\get_class($item), $mappedBy);
            $reflection->setAccessible(true);
            // Translate the item
            $subTranslationArgs =
                (new TranslationArgs($item, $args->getSourceLocale(), $args->getTargetLocale()))
                    ->setTranslatedParent($newOwner)
                    ->setProperty($reflection)
            ;

            $itemTrans = $this->translator->processTranslation($subTranslationArgs);

            // Set the translated item new owner
            $reflection->setValue($itemTrans, $newOwner);
            $newCollection[$key] = $itemTrans;
        }

        return $newCollection;
    }
}
