<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

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
     * DoctrineObjectHandler constructor.
     *
     * @param Reader                 $reader
     * @param EntityManagerInterface $em
     * @param PropertyAccessor       $propertyAccessor
     */
    public function __construct(Reader $reader, EntityManagerInterface $em, PropertyAccessor $propertyAccessor)
    {
        $this->reader           = $reader;
        $this->em               = $em;
        $this->propertyAccessor = $propertyAccessor;
    }

    public function supports($data, \ReflectionProperty $property = null): bool
    {
        if (null === $property) {
            return false;
        }

        $propAnnotations = $this->reader->getPropertyAnnotations($property);
        foreach ($propAnnotations as $propAnnotation) {
            if (property_exists($propAnnotation, 'mappedBy') && null !== $propAnnotation->mappedBy) {
                return true;
            }
        }

        return false;
    }

    public function handleSharedAmongstTranslations($data, string $locale)
    {
        // @todo implement
    }

    public function handleEmptyOnTranslate($data, string $locale)
    {
        // @todo implement
    }

    public function translate($data, string $locale, \ReflectionProperty $property = null, $parent = null)
    {
        // $data is the child association
        $clone     = clone $data;

        // Get the correct parent association with the fieldName
        $fieldName = $property->name;
        $associations = $this->em->getClassMetadata(\get_class($clone))->getAssociationMappings();
        foreach ($associations as $association) {
            if ($fieldName === $association['inversedBy']) {
                $parentFieldName = $association['fieldName'];
            }
        }

        $clone->setLocale($locale);

        // Set the invertedAssociation with the clone parent.
        $this->propertyAccessor->setValue($clone, $parentFieldName, $parent);

        return $clone;
    }

}
