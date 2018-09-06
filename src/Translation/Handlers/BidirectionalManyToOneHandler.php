<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Utils\AnnotationHelper;

/**
 * Handles translation of ManyToOne relations.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class BidirectionalManyToOneHandler implements TranslationHandlerInterface
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
     * @var BidirectionalAssociationHandler
     */
    private $bidirectionalAssociationHandler;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * BidirectionalManyToOneHandler constructor.
     *
     * @param AnnotationHelper                $annotationHelper
     * @param Reader                          $reader
     * @param BidirectionalAssociationHandler $bidirectionalAssociationHandler
     * @param EntityManagerInterface          $em
     * @param PropertyAccessorInterface       $propertyAccessor
     */
    public function __construct(
        AnnotationHelper $annotationHelper,
        Reader $reader,
        BidirectionalAssociationHandler $bidirectionalAssociationHandler,
        EntityManagerInterface $em,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->annotationHelper                = $annotationHelper;
        $this->reader                          = $reader;
        $this->bidirectionalAssociationHandler = $bidirectionalAssociationHandler;
        $this->em                              = $em;
        $this->propertyAccessor                = $propertyAccessor;
    }

    public function supports(TranslationArgs $args): bool
    {
        if ($args->getProperty() && $this->annotationHelper->isManyToOne($args->getProperty())) {
            $propAnnotations = $this->reader->getPropertyAnnotations($args->getProperty());
            foreach ($propAnnotations as $propAnnotation) {
                if (property_exists($propAnnotation, 'inversedBy') && null !== $propAnnotation->inversedBy) {
                    return true;
                }
            }
        }

        return false;
    }

    public function handleSharedAmongstTranslations(TranslationArgs $args)
    {
        // TODO AGU : Implement handleSharedAmongstTranslations() method.
    }

    public function handleEmptyOnTranslate(TranslationArgs $args)
    {
        // TODO AGU : Implement handleEmptyOnTranslate() method.
    }

    public function translate(TranslationArgs $args)
    {
        // $data is the child association
        $clone = clone $args->getDataToBeTranslated();

        // Get the correct parent association with the fieldName
        $fieldName    = $args->getProperty()->name;
        $associations = $this->em->getClassMetadata(\get_class($clone))->getAssociationMappings();

        foreach ($associations as $key => $association) {
            if ($fieldName === $key) {
                $parentFieldName = $association['fieldName'];
            }
        }

        $clone->setLocale($args->getTargetLocale());

        // Set the invertedAssociation with the clone parent.
        $this->propertyAccessor->setValue($clone, $parentFieldName, $args->getTranslatedParent());

        return $clone;
    }
}
