<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\PersistentCollection;
use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Translation\EntityTranslator;
use Umanit\TranslationBundle\Utils\AttributeHelper;

/**
 * Used for ManyToMany unidirectional association.
 */
class UnidirectionalManyToManyHandler implements TranslationHandlerInterface
{
    private AttributeHelper $attributeHelper;
    private EntityTranslator $translator;
    private EntityManagerInterface $em;

    public function __construct(
        AttributeHelper $attributeHelper,
        EntityTranslator $translator,
        EntityManagerInterface $em
    ) {
        $this->attributeHelper = $attributeHelper;
        $this->translator = $translator;
        $this->em = $em;
    }

    public function supports(TranslationArgs $args): bool
    {
        if (!$args->getDataToBeTranslated() instanceof Collection) {
            return false;
        }

        if ($args->getProperty() && $this->attributeHelper->isManyToMany($args->getProperty())) {
            $arguments = $args->getProperty()->getAttributes(ManyToMany::class)[0]->getArguments();

            if ((array_key_exists('mappedBy', $arguments) && null !== $arguments['mappedBy']) ||
                (array_key_exists('inversedBy', $arguments) && null !== $arguments['inversedBy'])) {
                return true;
            }
        }

        return false;
    }

    public function handleSharedAmongstTranslations(TranslationArgs $args)
    {
        return $this->translate($args);
    }

    public function handleEmptyOnTranslate(TranslationArgs $args)
    {
        return new ArrayCollection();
    }

    public function translate(TranslationArgs $args)
    {
        $newOwner = $args->getTranslatedParent();

        // Get the owner's fieldName
        $associations = $this->em->getClassMetadata(\get_class($newOwner))->getAssociationMappings();
        $association = $associations[$args->getProperty()->name];
        $fieldName = $association['fieldName'];

        $reflection = new \ReflectionProperty(\get_class($newOwner), $fieldName);

        $reflection->setAccessible(true);

        /** @var PersistentCollection $collection */
        $collection = $reflection->getValue($newOwner);

        foreach ($collection as $key => $item) {
            $collection->remove($key);
        }

        foreach ($args->getDataToBeTranslated() as $itemtoBeTranslated) {
            $itemTrans = $this->translator->translate($itemtoBeTranslated, $args->getTargetLocale());

            if (!$collection->contains($itemTrans)) {
                $collection->add($itemTrans);
            }
        }

        return $collection;
    }
}
