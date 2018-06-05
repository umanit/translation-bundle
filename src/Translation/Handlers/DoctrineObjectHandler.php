<?php


namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Umanit\TranslationBundle\Translation\EntityTranslator;
use Umanit\TranslationBundle\Translation\Pool\TranslationPool;

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
     * @param TranslationPool        $pool
     */
    public function __construct(EntityManagerInterface $em, EntityTranslator $translator)
    {
        $this->em         = $em;
        $this->translator = $translator;
    }

    public function supports($data, \ReflectionProperty $property = null): bool
    {
        if (\is_object($data)) {
            $data = ($data instanceof Proxy)
                ? get_parent_class($data)
                : \get_class($data);
        }

        return !$this->em->getMetadataFactory()->isTransient($data);
    }

    public function handleSharedAmongstTranslations($data, string $locale)
    {
        return $data;
    }

    public function handleEmptyOnTranslate($data, string $locale)
    {
        return null;
    }

    public function translate($data, string $locale, \ReflectionProperty $property = null, $parent = null)
    {
        $clone = clone $data;

        $this->translateProperties($clone, $locale, $clone);

        $this->em->persist($clone);

        return $clone;
    }

    /**
     * Loops through all object properties to translate them.
     *
     * @param object $clone
     * @param string $locale
     * @param null   $parent
     */
    public function translateProperties($clone, string $locale, $parent = null)
    {
        $accessor     = PropertyAccess::createPropertyAccessor();
        $properties   = $this->em->getClassMetadata(\get_class($clone))->getReflectionProperties();

        // Loop through all properties
        foreach ($properties as $property) {
            $propValue = $accessor->getValue($clone, $property->name);
            if (null === $propValue) {
                continue;
            }
            $propertyTranslation = $this->translator->translate($propValue, $locale, $property, $parent);
            $accessor->setValue($clone, $property->name, $propertyTranslation);
        }
    }
}
