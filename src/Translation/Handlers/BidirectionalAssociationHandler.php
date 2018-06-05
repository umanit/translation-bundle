<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Utils\AnnotationHelper;

/**
 * Handles translation of one-to-one-bidirectional association.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class BidirectionalAssociationHandler implements TranslationHandlerInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;
    /**
     * @var AnnotationHelper
     */
    private $annotationHelper;

    /**
     * DoctrineObjectHandler constructor.
     *
     * @param Reader                 $reader
     * @param EntityManagerInterface $em
     * @param PropertyAccessor       $propertyAccessor
     * @param AnnotationHelper       $annotationHelper
     */
    public function __construct(
        Reader $reader,
        EntityManagerInterface $em,
        PropertyAccessor $propertyAccessor,
        AnnotationHelper $annotationHelper
    ) {
        $this->reader           = $reader;
        $this->em               = $em;
        $this->propertyAccessor = $propertyAccessor;
        $this->annotationHelper = $annotationHelper;
    }

    public function supports(TranslationArgs $args): bool
    {
        if (null === $args->getProperty()) {
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
        if (true === $this->annotationHelper->isOneToOne($args->getProperty())) {
            $data    = $args->getDataToBeTranslated();
            $message =
                '%class%::%prop% is a Bidirectional OneToOne, it cannot be shared '.
                'amongst translations. Either remove the @SharedAmongstTranslation '.
                'annotation or choose another association type.';

            throw new \ErrorException(strtr($message, [
                '%class%' => \get_class($data),
                '%prop%'  => $args->getProperty()->name,
            ]));
        }

        return $args->getDataToBeTranslated();
    }

    public function handleEmptyOnTranslate(TranslationArgs $args)
    {
        return null;
    }

    public function translate(TranslationArgs $args)
    {
        // $data is the child association
        $clone = clone $args->getDataToBeTranslated();

        // Get the correct parent association with the fieldName
        $fieldName    = $args->getProperty()->name;
        $associations = $this->em->getClassMetadata(\get_class($clone))->getAssociationMappings();
        foreach ($associations as $association) {
            if ($fieldName === $association['inversedBy']) {
                $parentFieldName = $association['fieldName'];
            }
        }

        $clone->setLocale($args->getTargetLocale());

        // Set the invertedAssociation with the clone parent.
        $this->propertyAccessor->setValue($clone, $parentFieldName, $args->getTranslatedParent());

        return $clone;
    }

}
