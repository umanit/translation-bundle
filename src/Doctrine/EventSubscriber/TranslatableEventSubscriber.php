<?php

namespace Umanit\TranslationBundle\Doctrine\EventSubscriber;

use Doctrine\Common;
use Doctrine\ORM;
use Ramsey\Uuid\Uuid;
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
        return [
            ORM\Events::postUpdate,
            ORM\Events::postPersist,
            ORM\Events::postRemove,
            ORM\Events::prePersist,
            Orm\Events::loadClassMetadata,
        ];
    }

    /**
     * Adds a unique constraint on uuid and
     * locale for every translatable entity.
     *
     * @param ORM\Event\LoadClassMetadataEventArgs $eventArgs
     *
     * @throws \ReflectionException
     */
    public function loadClassMetadata(ORM\Event\LoadClassMetadataEventArgs $eventArgs)
    {
        $entityName = $eventArgs->getClassMetadata()->rootEntityName;

        // Create reflection from entity name
        $r = new \ReflectionClass($entityName);
        if ($r->implementsInterface(TranslatableInterface::class)) {
            $classMetadata = $eventArgs->getClassMetadata();
            $table         = $classMetadata->table;

            if (isset($table['uniqueConstraints'])) {
                return;
            }

            $table['uniqueConstraints'] = [
                $classMetadata->getTableName().'_unique_translation' => [
                    'columns' => ['uuid', 'locale'],
                ],
            ];
            $classMetadata->table       = $table;
        }
    }

    /**
     * @param ORM\Event\LifecycleEventArgs $args
     *
     * @throws ORM\OptimisticLockException
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
        $this->setDefaultValues($args);
    }

    /**
     * @param ORM\Event\LifecycleEventArgs $args
     *
     * @throws ORM\OptimisticLockException
     */
    public function postPersist(ORM\Event\LifecycleEventArgs $args)
    {
        $this->updateTranslations($args);
        $this->synchronizeTranslatableSharedField($args);
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
    public function setDefaultValues(ORM\Event\LifecycleEventArgs $args)
    {
        $translatable = $args->getEntity();
        if ($translatable instanceof TranslatableInterface && null === $translatable->getLocale()) {
            $translatable->setLocale($this->defaultLocale);
        }
        if ($translatable instanceof TranslatableInterface && null === $translatable->getUuid()) {
            $translatable->setUuid((string) Uuid::uuid4());
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
            $repo = $em->getRepository(\get_class($translatable));

            /** @var TranslatableInterface[] $translations */
            $translations = $repo->findBy(['uuid' => $translatable->getUuid()]);

            foreach ($translations as $translation) {
                $em->remove($translation);
            }
        }
    }

    /**
     * Updates the "translations" array of all translations on persist.
     *
     * @param ORM\Event\LifecycleEventArgs $args
     *
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     */
    public function updateTranslations(ORM\Event\LifecycleEventArgs $args)
    {
        $translatable = $args->getEntity();

        if ($translatable instanceof TranslatableInterface) {
            $em = $args->getEntityManager();
            // Gets all translations
            $repo = $em->getRepository(\get_class($translatable));

            /** @var TranslatableInterface[] $translations */
            $translations      = $repo->findBy(['uuid' => $translatable->getUuid()]);
            $translationsArray = [];

            foreach ($translations as $translation) {
                $translationsArray[] = $translation->getLocale();
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
     *
     * @throws ORM\OptimisticLockException
     */
    protected function synchronizeTranslatableSharedField(ORM\Event\LifecycleEventArgs $args)
    {
        $translatable = $args->getEntity();
        // Only synchronize TranslatableInterface
        if (!$translatable instanceof TranslatableInterface) {
            return;
        }
        $em         = $args->getEntityManager();
        $properties = $em->getClassMetadata(\get_class($translatable))->getReflectionProperties();

        $sharedAmongstTranslationsProperties = array_filter($properties, function ($property) {
            // @todo AGU : ManyToMany are not supported yet
            return $this->isSharedAmongstTranslations($property) && $this->isNotManyToMany($property);
        });

        // Update the translations if any property is to be shared
        if (empty($sharedAmongstTranslationsProperties)) {
            return;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $em               = $args->getEntityManager();
        $translations     = $em
            ->getRepository(\get_class($translatable))
            ->findBy(['uuid' => $translatable->getUuid()])
        ;

        // Loops through all translations
        foreach ($translations as $translation) {
            // Make sure we don't update the currently updated entity
            if ($translation === $translatable) {
                continue;
            }
            foreach ($sharedAmongstTranslationsProperties as $property) {
                $sourceValue      = $propertyAccessor->getValue($translatable, $property->name);
                $translationValue = $propertyAccessor->getValue($translation, $property->name);
                // Set the value only of it's not already the same
                if ($translationValue === $sourceValue) {
                    continue;
                }
                // If property is translatable, check for its translation
                if ($translationValue instanceof TranslatableInterface) {
                    $sourceValue = $em
                        ->getRepository(\get_class($sourceValue))
                        ->findOneBy([
                            'uuid'   => $translationValue->getUuid(),
                            'locale' => $translationValue->getLocale(),
                        ])
                    ;
                }
                $propertyAccessor->setValue($translation, $property->name, $sourceValue);
            }
            $em->persist($translation);
            $em->flush($translation);
        }
    }

    /**
     * Defines if the property is to be shared amongst translations.
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    protected function isSharedAmongstTranslations(\ReflectionProperty $property): bool
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
    protected function isNotManyToMany(\ReflectionProperty $property): bool
    {
        return null === $this->reader->getPropertyAnnotation($property, ORM\Mapping\ManyToMany::class);
    }
}
