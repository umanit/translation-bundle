<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Proxy;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Translation\EntityTranslator;

/**
 * Handles basic Doctrine Objects.
 * Usually the entry point of a translation.
 */
class DoctrineObjectHandler implements TranslationHandlerInterface
{
    protected EntityManagerInterface $em;
    protected EntityTranslator $translator;

    public function __construct(EntityManagerInterface $em, EntityTranslator $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    public function supports(TranslationArgs $args): bool
    {
        $data = $args->getDataToBeTranslated();

        if (\is_object($data)) {
            $data = ($data instanceof Proxy) ?
                get_parent_class($data) :
                \get_class($data);
        }

        return !$this->em->getMetadataFactory()->isTransient($data);
    }

    public function handleSharedAmongstTranslations(TranslationArgs $args)
    {
        return $args->getDataToBeTranslated();
    }

    public function handleEmptyOnTranslate(TranslationArgs $args)
    {
        return null;
    }

    public function translate(TranslationArgs $args)
    {
        $clone = clone $args->getDataToBeTranslated();

        $args->setDataToBeTranslated($clone);
        $this->translateProperties($args);

        return $args->getDataToBeTranslated();
    }

    /**
     * Loops through all object properties to translate them.
     */
    public function translateProperties(TranslationArgs $args)
    {
        $translation = $args->getDataToBeTranslated();
        $accessor = PropertyAccess::createPropertyAccessor();
        $reflect = new \ReflectionClass(\get_class($args->getDataToBeTranslated()));
        $properties = $reflect->getProperties();

        // Loop through all properties
        foreach ($properties as $property) {
            $propValue = $accessor->getValue($args->getDataToBeTranslated(), $property->name);

            if (empty($propValue) || ($propValue instanceof Collection && $propValue->isEmpty())) {
                continue;
            }

            $subTranslationArgs =
                (new TranslationArgs($propValue, $args->getSourceLocale(), $args->getTargetLocale()))
                    ->setTranslatedParent($translation)
                    ->setProperty($property)
            ;

            $propertyTranslation = $this->translator->processTranslation($subTranslationArgs);

            try {
                $accessor->setValue($translation, $property->name, $propertyTranslation);
            } catch (NoSuchPropertyException $e) {
                $reflection = new \ReflectionProperty(\get_class($translation), $property->name);

                $reflection->setAccessible(true);

                $reflection->setValue($translation, $propertyTranslation);
            }
        }
    }
}
