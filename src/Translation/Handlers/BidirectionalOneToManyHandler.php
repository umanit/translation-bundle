<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\OneToMany;
use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Translation\EntityTranslator;
use Umanit\TranslationBundle\Utils\AttributeHelper;

/**
 * Handles translation of OneToMany relations.
 */
class BidirectionalOneToManyHandler implements TranslationHandlerInterface
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
        if ($args->getProperty() && $this->attributeHelper->isOneToMany($args->getProperty())) {
            $arguments = $args->getProperty()->getAttributes(OneToMany::class)[0]->getArguments();

            if (array_key_exists('mappedBy', $arguments) && null !== $arguments['mappedBy']) {
                return true;
            }
        }

        return false;
    }

    public function handleSharedAmongstTranslations(TranslationArgs $args)
    {
        $data = $args->getDataToBeTranslated();
        $message =
            '%class%::%prop% is a Bidirectional OneToMany, it cannot be shared '.
            'amongst translations. Either remove the SharedAmongstTranslation '.
            'attribute or choose another association type.';

        throw new \ErrorException(
            strtr($message, [
                '%class%' => \get_class($data),
                '%prop%'  => $args->getProperty()->name,
            ])
        );
    }

    public function handleEmptyOnTranslate(TranslationArgs $args)
    {
        return new ArrayCollection();
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
        // their owner to $newOwner
        foreach ($newCollection as $key => $item) {
            $reflection = new \ReflectionProperty(\get_class($item), $mappedBy);

            $reflection->setAccessible(true);

            // Translate the item
            $subTranslationArgs = (new TranslationArgs($item, $args->getSourceLocale(), $args->getTargetLocale()))
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
