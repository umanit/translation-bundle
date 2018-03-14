<?php

namespace Umanit\TranslationBundle\Doctrine\EventSubscriber;

use \Doctrine\ORM;
use \Doctrine\Common;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Umanit\TranslationBundle\Doctrine\Annotation\SharedAmongstTranslations;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class TranslatableEventSubscriber implements Common\EventSubscriber
{
    /**
     * @var Common\Annotations\Reader
     */
    private $reader;

    /**
     * @var
     */
    private $defaultLocale;

    /**
     * TranslatableEventSubscriber constructor.
     *
     * @param Common\Annotations\Reader $reader
     * @param string                    $defaultLocale
     */
    public function __construct(Common\Annotations\Reader $reader, $defaultLocale)
    {
        $this->reader        = $reader;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [ORM\Events::postUpdate, ORM\Events::postPersist, ORM\Events::postRemove, ORM\Events::prePersist];
    }

    /**
     * @param ORM\Event\LifecycleEventArgs $args
     */
    public function postUpdate(ORM\Event\LifecycleEventArgs $args)
    {
        $this->synchronizeTranslatableSharedField($args);
    }

    /**
     * @param ORM\Event\LifecycleEventArgs $args
     */
    public function prePersist(ORM\Event\LifecycleEventArgs $args)
    {
        $this->setDefaultLocale($args);
    }

    /**
     * @param ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(ORM\Event\LifecycleEventArgs $args)
    {
        $this->updateTranslations($args);
        $this->setDefaultOid($args);
    }

    /**
     * @inheritdoc.
     *
     * @param ORM\Event\LifecycleEventArgs $args
     */
    public function postRemove(ORM\Event\LifecycleEventArgs $args)
    {
        // @todo AGU : make this configurable instead
        // $this->removeAllTranslations($args);
    }

    /**
     * Sets the default locale before persist.
     *
     * @param ORM\Event\LifecycleEventArgs $args
     */
    public function setDefaultLocale(ORM\Event\LifecycleEventArgs $args)
    {
        $translatable = $args->getEntity();
        if ($translatable instanceof TranslatableInterface && $translatable->getLocale() === null) {
            $translatable->setLocale($this->defaultLocale);
        }
    }

    /**
     * Sets the default OID after persist.
     *
     * @param ORM\Event\LifecycleEventArgs $args
     */
    public function setDefaultOid(ORM\Event\LifecycleEventArgs $args)
    {
        $translatable = $args->getEntity();
        if ($translatable instanceof TranslatableInterface && $translatable->getOid() === null) {
            $translatable->setOid($translatable->getId());
            $args->getEntityManager()->persist($translatable);
            $args->getEntityManager()->flush($translatable);
        }
    }

    /**
     * Removes all translations of a TranslatableInterface.
     *
     * @param ORM\Event\LifecycleEventArgs $args
     */
    public function removeAllTranslations(ORM\Event\LifecycleEventArgs $args)
    {
        $translatable = $args->getEntity();
        if ($translatable instanceof TranslatableInterface) {
            $em = $args->getEntityManager();
            // Gets all translations
            $repo = $em->getRepository(get_class($translatable));

            /** @var TranslatableInterface[] $translations */
            $translations = $repo->findBy(['oid' => $translatable->getOid()]);

            foreach ($translations as $translation) {
                $em->remove($translation);
            }
        }
    }

    /**
     * Updates the "translations" array of all translations on persist.
     *
     * @param ORM\Event\LifecycleEventArgs $args
     */
    public function updateTranslations(ORM\Event\LifecycleEventArgs $args)
    {
        $translatable = $args->getEntity();

        if ($translatable instanceof TranslatableInterface) {
            $em = $args->getEntityManager();
            // Gets all translations
            $repo = $em->getRepository(get_class($translatable));

            /** @var TranslatableInterface[] $translations */
            $translations      = $repo->findBy(['oid' => $translatable->getOid()]);
            $translationsArray = [];

            foreach ($translations as $translation) {
                $translationsArray[$translation->getLocale()] = $translation->getId();
            }

            foreach ($translations as $translation) {
                $translation->setTranslations($translationsArray);
                $args->getEntityManager()->persist($translation);

                $em->flush($translation);
            }
        }
    }

    /**
     * Seeks for the @SharedAmongstTranslations() annotation and sychronize all translations.
     *
     * @param ORM\Event\LifecycleEventArgs $args
     */
    protected function synchronizeTranslatableSharedField(ORM\Event\LifecycleEventArgs $args)
    {
        $translatable = $args->getEntity();

        if ($translatable instanceof TranslatableInterface) {
            $em = $args->getEntityManager();
            $properties = $em->getClassMetadata($translatable)->getFieldNames();

            $sharedAmongstTranslationsProperties = array_filter($properties, function ($property) {
                // @todo AGU : ManyToMany are not supported yet
                return $this->isSharedAmongstTranslations($property) && $this->isNotManyToMany($property);
            });

            // Update the translations if any property is to be shared
            if (!empty($sharedAmongstTranslationsProperties)) {
                // Finds all translations
                $em               = $args->getEntityManager();
                $repo             = $em->getRepository(get_class($translatable));
                $propertyAccessor = PropertyAccess::createPropertyAccessor();
                $translations     = $repo->findBy(['oid' => $translatable->getOid()]);

                foreach ($translations as $translation) {
                    // Make sure we don't update the currently updated entity
                    if ($translation !== $translatable) {
                        foreach ($sharedAmongstTranslationsProperties as $property) {
                            $sourceValue      = $propertyAccessor->getValue($translatable, $property->name);
                            $translationValue = $propertyAccessor->getValue($translation, $property->name);
                            // Set the value only of it's not already the same
                            if ($translationValue !== $sourceValue) {
                                // If property is translatable, check for it's translation
                                if ($translationValue instanceof TranslatableInterface) {
                                    $sourceValue = $args
                                        ->getEntityManager()
                                        ->getRepository(get_class($sourceValue))
                                        ->findOneBy([
                                            'oid'    => $translationValue->getOid(),
                                            'locale' => $translationValue->getLocale(),
                                        ])
                                    ;
                                }
                                $propertyAccessor->setValue($translation, $property->name, $sourceValue);
                            }
                        }
                        $em->persist($translation);
                        $em->flush($translation);
                    }
                }
            }
        }
    }

    /**
     * Defines if the property is to be shared amongst translations.
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
     * Defines if the property is ManyToMany.
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    protected function isNotManyToMany(\ReflectionProperty $property)
    {
        return null === $this->reader->getPropertyAnnotation($property, ORM\Mapping\ManyToMany::class);
    }
}
