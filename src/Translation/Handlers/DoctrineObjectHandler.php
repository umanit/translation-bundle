<?php


namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Translation\EntityTranslator;

/**
 * Handles basic Doctrine Object.
 * Usual the entry point of a translation.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class DoctrineObjectHandler implements TranslationHandlerInterface
{
    /** @var EntityManagerInterface */
    protected $em;

    /**
     * @var EntityTranslator
     */
    protected $translator;

    /**
     * DoctrineObjectHandler constructor.
     *
     * @param EntityManagerInterface $em
     * @param EntityTranslator       $translator
     */
    public function __construct(EntityManagerInterface $em, EntityTranslator $translator)
    {
        $this->em         = $em;
        $this->translator = $translator;
    }

    public function supports(TranslationArgs $args): bool
    {
        $data = $args->getDataToBeTranslated();

        if (\is_object($data)) {
            $data = ($data instanceof Proxy)
                ? get_parent_class($data)
                : \get_class($data);
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
     *
     * @param TranslationArgs $args
     */
    public function translateProperties(TranslationArgs $args)
    {
        $translation = $args->getDataToBeTranslated();
        $accessor    = PropertyAccess::createPropertyAccessor();
        $properties  = $this->em->getClassMetadata(\get_class($args->getDataToBeTranslated()))->getReflectionProperties();

        // Loop through all properties
        foreach ($properties as $property) {
            $propValue = $accessor->getValue($args->getDataToBeTranslated(), $property->name);
            if (null === $propValue) {
                continue;
            }
            $subTranslationArgs =
                (new TranslationArgs($propValue, $args->getSourceLocale(), $args->getTargetLocale()))
                    ->setTranslatedParent($translation)
                    ->setProperty($property)
            ;

            $propertyTranslation = $this->translator->processTranslation($subTranslationArgs);

            $reflection = new \ReflectionProperty(\get_class($translation), $property->name);
            $reflection->setAccessible(true);
            $reflection->setValue($translation, $propertyTranslation);
        }
    }
}
