<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Translation\EntityTranslator;
use Umanit\TranslationBundle\Utils\AttributeHelper;

/**
 * Handles translation of ManyToOne relations.
 */
class BidirectionalManyToOneHandler implements TranslationHandlerInterface
{
    private AttributeHelper $attributeHelper;
    private EntityManagerInterface $em;
    private PropertyAccessorInterface $propertyAccessor;
    private EntityTranslator $translator;

    public function __construct(
        AttributeHelper $attributeHelper,
        EntityManagerInterface $em,
        PropertyAccessorInterface $propertyAccessor,
        EntityTranslator $translator
    ) {
        $this->attributeHelper = $attributeHelper;
        $this->em = $em;
        $this->propertyAccessor = $propertyAccessor;
        $this->translator = $translator;
    }

    public function supports(TranslationArgs $args): bool
    {
        if ($args->getProperty() && $this->attributeHelper->isManyToOne($args->getProperty())) {
            $arguments = $args->getProperty()->getAttributes(ManyToOne::class)[0]->getArguments();

            if (array_key_exists('inversedBy', $arguments) && null !== $arguments['inversedBy']) {
                return true;
            }
        }

        return false;
    }

    public function handleSharedAmongstTranslations(TranslationArgs $args)
    {
        $data = $args->getDataToBeTranslated();
        $message =
            '%class%::%prop% is a Bidirectional ManyToOne, it cannot be shared '.
            'amongst translations. Either remove the @SharedAmongstTranslation '.
            'annotation or choose another association type.';

        throw new \ErrorException(
            strtr($message, [
                '%class%' => \get_class($data),
                '%prop%'  => $args->getProperty()->name,
            ])
        );
    }

    public function handleEmptyOnTranslate(TranslationArgs $args)
    {
        return null;
    }

    public function translate(TranslationArgs $args)
    {
        // $data is the child association
        $clone = clone $args->getDataToBeTranslated();
        $parentFieldName = null;

        // Get the correct parent association with the fieldName
        $fieldName = $args->getProperty()->name;
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
