<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Translation\EntityTranslator;
use Umanit\TranslationBundle\Utils\AttributeHelper;

/**
 * Collection handler, used for ManyToMany bidirectional association.
 */
class CollectionHandler implements TranslationHandlerInterface
{
    private AttributeHelper $attributeHelper;
    private EntityManagerInterface $em;
    private EntityTranslator $translator;

    public function __construct(
        AttributeHelper $attributeHelper,
        EntityManagerInterface $em,
        EntityTranslator $translator
    ) {
        $this->attributeHelper = $attributeHelper;
        $this->em = $em;
        $this->translator = $translator;
    }

    public function supports(TranslationArgs $args): bool
    {
        if (!$args->getDataToBeTranslated() instanceof Collection) {
            return false;
        }

        if ($args->getProperty() && $this->attributeHelper->isManyToMany($args->getProperty())) {
            $arguments = $args->getProperty()->getAttributes(ManyToMany::class)[0]->getArguments();

            if (array_key_exists('mappedBy', $arguments) && null !== $arguments['mappedBy']) {
                return true;
            }
        }

        return false;
    }

    public function handleSharedAmongstTranslations(TranslationArgs $args)
    {
        /** @var Collection $collection */
        $collection = $args->getDataToBeTranslated();
        $newCollection = clone $collection;
        $newOwner = $args->getTranslatedParent();

        // Get the owner's "mappedBy"
        $associations = $this->em->getClassMetadata(\get_class($newOwner))->getAssociationMappings();
        $association = $associations[$args->getProperty()->name];
        $mappedBy = $association['mappedBy'];

        // Iterate through collection and set
        // their owner to $newOwner
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
            $reflection->setValue($itemTrans, new ArrayCollection([$newOwner]));
            $newCollection[$key] = $itemTrans;
        }

        return $newCollection;
    }

    public function handleEmptyOnTranslate(TranslationArgs $args)
    {
        return new ArrayCollection([]);
    }

    public function translate(TranslationArgs $args)
    {
        /** @var Collection $collection */
        $collection = $args->getDataToBeTranslated();
        $newCollection = clone $collection;
        $newOwner = $args->getTranslatedParent();

        // Get the owner's "mappedBy"
        $associations = $this->em->getClassMetadata(\get_class($newOwner))->getAssociationMappings();
        $association = $associations[$args->getProperty()->name];
        $mappedBy = $association['mappedBy'];

        // Iterate through collection and set
        // their owner owner to $newOwner
        foreach ($newCollection as $key => $item) {
            $reflection = new \ReflectionProperty(\get_class($item), $mappedBy);

            $reflection->setAccessible(true);

            // Set item's owner to null
            $reflection->setValue($item, new ArrayCollection([]));

            // Translate the item
            $itemTrans = $this->translator->translate($item, $args->getTargetLocale());

            // Set the translated item new owner
            $reflection->setValue($itemTrans, new ArrayCollection([$newOwner]));
            $newCollection[$key] = $itemTrans;
        }

        return $newCollection;
    }
}
