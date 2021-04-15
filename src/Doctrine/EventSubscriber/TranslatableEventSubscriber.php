<?php

namespace Umanit\TranslationBundle\Doctrine\EventSubscriber;

use Doctrine\Common;
use Doctrine\ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Umanit\TranslationBundle\Doctrine\Annotation\SharedAmongstTranslations;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Translation\EntityTranslator;

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
     * @var string
     */
    private $defaultLocale;

    /**
     * @var EntityTranslator
     */
    private $translator;

    /**
     * @var array
     */
    private $alreadySyncedEntities = [];

    /**
     * TranslatableEventSubscriber constructor.
     *
     * @param Common\Annotations\Reader $reader
     * @param string                    $defaultLocale
     */
    public function __construct(
        Common\Annotations\Reader $reader,
        string $defaultLocale
    ) {
        $this->reader        = $reader;
        $this->defaultLocale = $defaultLocale;
    }

    public function setEntityTranslator(EntityTranslator $entityTranslator)
    {
        $this->translator = $entityTranslator;
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
                    'columns' => ['tuuid', 'locale'],
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
        if (\in_array($args->getEntity(), $this->alreadySyncedEntities, true)) {
            return;
        }
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
        $this->updateTranslations($args);

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
        if ($translatable instanceof TranslatableInterface && null === $translatable->getTuuid()) {
            $translatable->setTuuid((string) Uuid::uuid4());
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
            $translations = $repo->findBy(['tuuid' => $translatable->getTuuid()]);

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
            $translations      = $repo->findBy(['tuuid' => $translatable->getTuuid()]);
            $translationsArray = [];

            foreach ($translations as $translation) {
                $translationsArray[] = $translation->getLocale();
            }

            foreach ($translations as $translation) {
                $translation->setTranslations($translationsArray);
                $args->getEntityManager()->persist($translation);
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
        $translatable                  = $args->getEntity();
        $this->alreadySyncedEntities[] = $translatable;

        // Only synchronize TranslatableInterface
        if (!$translatable instanceof TranslatableInterface) {
            return;
        }
        $em         = $args->getEntityManager();
        $properties = $em->getClassMetadata(\get_class($translatable))->getReflectionProperties();

        $sharedAmongstTranslationsProperties = array_filter($properties, function ($property) {
            return $this->isSharedAmongstTranslations($property);
        });

        // Update the translations if any property is to be shared
        if (empty($sharedAmongstTranslationsProperties)) {
            return;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $em               = $args->getEntityManager();
        $translations     = $em
            ->getRepository(\get_class($translatable))
            ->findBy(['tuuid' => $translatable->getTuuid()])
        ;

        // Loops through all translations
        foreach ($translations as $translation) {
            // Make sure we don't update the currently updated entity
            if ($translation === $translatable) {
                continue;
            }
            foreach ($sharedAmongstTranslationsProperties as $property) {
                $sourceValue = $propertyAccessor->getValue($translatable, $property->name);

                $translationArgs = (new TranslationArgs(
                    $sourceValue,
                    $translatable->getLocale(),
                    $translation->getLocale()
                ))
                    ->setProperty($property)
                    ->setTranslatedParent($translation)
                ;

                $translationValue = $this->translator->processTranslation($translationArgs);

                $propertyAccessor->setValue($translation, $property->name, $translationValue);
            }
            $em->persist($translation);
            $em->flush();
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
