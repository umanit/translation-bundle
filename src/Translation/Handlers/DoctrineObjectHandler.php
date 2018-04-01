<?php


namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Umanit\TranslationBundle\Translation\EntityTranslator;

/**
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

    public function supports($data): bool
    {
        if (is_object($data)) {
            $data = ($data instanceof Proxy)
                ? get_parent_class($data)
                : get_class($data);
        }

        return !$this->em->getMetadataFactory()->isTransient($data);
    }

    public function handleSharedAmongstTranslations($data)
    {
        // TODO: Implement handleSharedAmongstTranslations() method.

        return null;
    }

    public function handleEmptyOnTranslate($data)
    {
        // TODO: Implement handleEmptyOnTranslate() method.
        return null;
    }

    public function translate($data, string $locale)
    {
        $clone = clone $data;

        $this->translateProperties($clone, $locale);

        $this->em->persist($clone);

        return $clone;
    }

    /**
     * Loops through all object properties to translate them.
     *
     * @param object $clone
     * @param string $locale
     */
    public function translateProperties($clone, string $locale)
    {
        $accessor   = PropertyAccess::createPropertyAccessor();
        $properties = $this->em->getClassMetadata(get_class($clone))->getReflectionProperties();

        // Loop through all properties
        foreach ($properties as $property) {
            $propValue           = $accessor->getValue($clone, $property->name);
            $propertyTranslation = $this->translator->translate($propValue, $locale, $property);
            $accessor->setValue($clone, $property->name, $propertyTranslation);
        }
    }
}
