<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Translation\EntityTranslator;
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
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;
    /**
     * @var EntityTranslator
     */
    private $translator;

    /**
     * BidirectionalManyToOneHandler constructor.
     *
     * @param AnnotationHelper          $annotationHelper
     * @param Reader                    $reader
     * @param EntityManagerInterface    $em
     * @param PropertyAccessorInterface $propertyAccessor
     * @param EntityTranslator          $translator
     */
    public function __construct(
        AnnotationHelper $annotationHelper,
        Reader $reader,
        EntityManagerInterface $em,
        PropertyAccessorInterface $propertyAccessor,
        EntityTranslator $translator
    ) {
        $this->annotationHelper = $annotationHelper;
        $this->reader           = $reader;
        $this->em               = $em;
        $this->propertyAccessor = $propertyAccessor;
        $this->translator       = $translator;
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
        $data    = $args->getDataToBeTranslated();
        $message =
            '%class%::%prop% is a Bidirectional ManyToOne, it cannot be shared '.
            'amongst translations. Either remove the @SharedAmongstTranslation '.
            'annotation or choose another association type.';

        throw new \ErrorException(strtr($message, [
            '%class%' => \get_class($data),
            '%prop%'  => $args->getProperty()->name,
        ]));
    }

    public function handleEmptyOnTranslate(TranslationArgs $args)
    {
        return null;
    }

    public function translate(TranslationArgs $args)
    {
        // $data is the child association
        $clone           = clone $args->getDataToBeTranslated();
        $parentFieldName = null;

        // Get the correct parent association with the fieldName
        $fieldName    = $args->getProperty()->name;
        $associations = $this->em->getClassMetadata(\get_class($clone))->getAssociationMappings();

        foreach ($associations as $key => $association) {
            if ($fieldName === $key) {
                $parentFieldName = $association['fieldName'];
            }
        }

        if (null !== $parentFieldName) {
            $clone->setLocale($args->getTargetLocale());

            // Set the invertedAssociation with the clone parent.
            $this->propertyAccessor->setValue($clone, $parentFieldName, $args->getTranslatedParent());

            return $clone;
        }

        // If no parent field is found, were in the parent, translate it rather than the child.
        return $this->translator->translate($args->getDataToBeTranslated(), $args->getTargetLocale());
    }
}
