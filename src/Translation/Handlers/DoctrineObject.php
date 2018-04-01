<?php


namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Umanit\TranslationBundle\Translation\EntityTranslator;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class DoctrineObject implements TranslationHandlerInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /**
     * @var EntityTranslator
     */
    private $translator;

    /**
     * DoctrineObject constructor.
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

        $accessor   = PropertyAccess::createPropertyAccessor();
        $properties = $this->em->getClassMetadata(get_class($clone))->getReflectionProperties();

        // Loop through all properties
        foreach ($properties as $property) {
            $propValue = $accessor->getValue($clone, $property->name);
            $propertyTranslation = $this->translator->translate($propValue, $locale, $property);
            $accessor->setValue($clone, $property->name, $propertyTranslation);
        }

        $this->em->persist($clone);

        return $clone;
    }

}
