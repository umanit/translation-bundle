<?php

namespace Umanit\TranslationBundle\Translator;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\ReflectionEmbeddedProperty;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Umanit\TranslationBundle\Doctrine\Annotation\EmptyOnTranslate;
use Umanit\TranslationBundle\Doctrine\Annotation\SharedAmongstTranslations;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Event\TranslateEvent;

/**
 * @deprecated To be removed in 1.0
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class EntityTranslator
{
    /**
     * @var array
     */
    protected $locales;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * EntityTranslator constructor.
     *
     * @param array                    $locales
     * @param EntityManagerInterface   $em
     * @param Reader                   $reader
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        array $locales,
        EntityManagerInterface $em,
        Reader $reader,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->locales         = $locales;
        $this->em              = $em;
        $this->reader          = $reader;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Seeks for an entity in the required locale or creates it.
     *
     * @param object $entity
     * @param string                $locale
     *
     * @return object
     */
    public function getEntityTranslation($entity, $locale)
    {
        // @todo AGU : Check that locale exists
        return $this->translate($entity, $locale);
    }

    /**
     * @param mixed  $child
     *
     * @param string $locale
     *
     * @param null   $parent
     *
     * @return mixed
     */
    protected function translate($child, $locale, $parent = null)
    {
        if (!is_object($child) || $child instanceof \DateTime) {
            return $child;
        }

        if ($child instanceof TranslatableInterface) {
            // Tries to get the existing translation
            if ($existingTrans = $this->findTranslation($child, $locale)) {
                // There's already a translation set it to the child and continue
                return $existingTrans;
            }
        }

        // Otherwise, clone the property
        $clone = clone $child;
        if ($clone instanceof TranslatableInterface) {
            if (!$child->getOid()) {
                $child->setOid($child->getId());

                $this->em->persist($child);
                $this->em->flush($child);
            }

            $clone->setOid($child->getOid() ?: $child->getId());
            $clone->setLocale($locale);
        }

        if (method_exists($clone, 'setId')) {
            $clone->setId(null);
        }

        $this->eventDispatcher->dispatch(TranslateEvent::PRE_TRANSLATE, new TranslateEvent($child, $clone, $locale));

        $accessor = PropertyAccess::createPropertyAccessor();
        $properties = $this->em->getClassMetadata(get_class($clone))->getReflectionProperties();

        foreach ($properties as $property) {

            // No need to translate ReflectionEmbeddedProperty
            if ($property instanceof ReflectionEmbeddedProperty) {
                continue;
            }

            $propValue = $accessor->getValue($child, $property->name);

            // Embedded properties are simply copied
            if ($this->isEmbedded($property)) {
                $accessor->setValue($clone, $property->name, $propValue);
                continue;
            }

            if (!is_object($propValue) || $propValue === $parent) {
                continue;
            }

            // Check for SharedAmongstTranslations annotation
            if ($this->isSharedAmongstTranslations($property)) {
                $accessor->setValue($clone, $property->name, $propValue);
                continue;
            }

            // Check for EmptyOnTranslate annotation
            if ($this->isEmptyOnTranslate($property)) {
                $accessor->setValue($clone, $property->name, null);
                continue;
            }

            if (is_array($propValue) || $propValue instanceof \ArrayAccess) {
                $propTrans = clone $propValue;
                foreach ($propTrans as $key => $subProp) {
                    $propTrans[$key] = $this->translate($subProp, $locale, $child);
                }
            } else {
                $propTrans = $this->translate($propValue, $locale, $child);
            }

            // Then set the value to the main using property access
            $accessor->setValue($clone, $property->name, $propTrans);
        }

        $this->em->detach($child);
        $this->em->persist($clone);
        $this->em->flush($clone);

        $this->eventDispatcher->dispatch(TranslateEvent::POST_TRANSLATE, new TranslateEvent($child, $clone, $locale));

        return $clone;
    }

    /**
     * Defines if the property is embedded.
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    protected function isEmbedded(\ReflectionProperty $property)
    {
        return null !== $this->reader->getPropertyAnnotation($property, Embedded::class);
    }

    /**
     * Defines if the property is to be shared amongst parents' translations.
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    protected function isSharedAmongstTranslations(\ReflectionProperty $property)
    {
        return null !== $this->reader->getPropertyAnnotation($property, SharedAmongstTranslations::class);
    }

    /**
     * Defines if the property should be emptied on translate.
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    protected function isEmptyOnTranslate(\ReflectionProperty $property)
    {
        return null !== $this->reader->getPropertyAnnotation($property, EmptyOnTranslate::class);
    }

    /**
     * Returns a translatable translation.
     *
     * @param TranslatableInterface $entity
     * @param string                $locale
     *
     * @return null|object
     */
    protected function findTranslation(TranslatableInterface $entity, $locale)
    {
        return $this->em->getRepository(get_class($entity))->findOneBy([
            'locale' => $locale,
            'oid'    => $entity->getOid(),
        ]);
    }

}
