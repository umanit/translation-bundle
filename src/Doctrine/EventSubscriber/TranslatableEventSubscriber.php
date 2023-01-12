<?php

namespace Umanit\TranslationBundle\Doctrine\EventSubscriber;

use Doctrine\Common;
use Doctrine\ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Umanit\TranslationBundle\Doctrine\Attribute\SharedAmongstTranslations;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Translation\EntityTranslator;

class TranslatableEventSubscriber implements Common\EventSubscriber
{
    private ?string $defaultLocale;
    private EntityTranslator $translator;
    private array $alreadySyncedEntities = [];

    public function __construct(string $defaultLocale = null)
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function setEntityTranslator(EntityTranslator $entityTranslator)
    {
        $this->translator = $entityTranslator;
    }

    public function getSubscribedEvents()
    {
        return [
            Orm\Events::loadClassMetadata,
            ORM\Events::prePersist,
            ORM\Events::postPersist,
            ORM\Events::postUpdate,
            ORM\Events::postRemove,
        ];
    }

    /**
     * Adds a unique constraint on uuid and
     * locale for every translatable entity.
     *
     * @throws \ReflectionException
     */
    public function loadClassMetadata(ORM\Event\LoadClassMetadataEventArgs $eventArgs)
    {
        $entityName = $eventArgs->getClassMetadata()->rootEntityName;

        // Create reflection from entity name
        $r = new \ReflectionClass($entityName);

        if ($r->implementsInterface(TranslatableInterface::class) && !$r->isAbstract()) {
            $classMetadata = $eventArgs->getClassMetadata();
            $table = $classMetadata->table;

            if (isset($table['uniqueConstraints'])) {
                return;
            }

            $table['uniqueConstraints'] = [
                $classMetadata->getTableName().'_unique_translation' => [
                    'columns' => ['tuuid', 'locale'],
                ],
            ];
            $classMetadata->table = $table;
        }
    }

    public function prePersist(ORM\Event\LifecycleEventArgs $args)
    {
        $this->setDefaultValues($args);
    }

    /**
     * @throws ORM\OptimisticLockException
     */
    public function postPersist(ORM\Event\LifecycleEventArgs $args)
    {
        $this->updateTranslations($args);
        $this->synchronizeTranslatableSharedField($args);
    }

    /**
     * @throws ORM\OptimisticLockException
     */
    public function postUpdate(ORM\Event\LifecycleEventArgs $args)
    {
        if (\in_array($args->getObject(), $this->alreadySyncedEntities, true)) {
            return;
        }

        $this->synchronizeTranslatableSharedField($args);
    }

    public function postRemove(ORM\Event\LifecycleEventArgs $args)
    {
        $this->updateTranslations($args);

        // @todo AGU : make this configurable instead
        // $this->removeAllTranslations($args);
    }

    /**
     * Sets the default locale before persist.
     */
    public function setDefaultValues(ORM\Event\LifecycleEventArgs $args)
    {
        $translatable = $args->getObject();

        if ($translatable instanceof TranslatableInterface && null === $translatable->getLocale()) {
            $translatable->setLocale($this->defaultLocale);
        }

        if ($translatable instanceof TranslatableInterface && null === $translatable->getTuuid()) {
            $translatable->setTuuid((string) Uuid::uuid4());
        }
    }

    /**
     * Removes all translations of a TranslatableInterface.
     */
    public function removeAllTranslations(ORM\Event\LifecycleEventArgs $args)
    {
        $translatable = $args->getObject();

        if ($translatable instanceof TranslatableInterface) {
            $em = $args->getObjectManager();
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
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     */
    public function updateTranslations(ORM\Event\LifecycleEventArgs $args)
    {
        $translatable = $args->getObject();

        if ($translatable instanceof TranslatableInterface) {
            $em = $args->getObjectManager();
            // Gets all translations
            $repo = $em->getRepository(\get_class($translatable));

            /** @var TranslatableInterface[] $translations */
            $translations = $repo->findBy(['tuuid' => $translatable->getTuuid()]);
            $translationsArray = [];

            foreach ($translations as $translation) {
                $translationsArray[] = $translation->getLocale();
            }

            foreach ($translations as $translation) {
                $translation->setTranslations($translationsArray);
                $args->getObjectManager()->persist($translation);
            }

            $args->getObjectManager()->flush();
        }
    }

    /**
     * Looks for the @SharedAmongstTranslations attribute and sychronizes all translations
     *
     * @throws ORM\OptimisticLockException
     */
    protected function synchronizeTranslatableSharedField(ORM\Event\LifecycleEventArgs $args)
    {
        $translatable = $args->getObject();
        $this->alreadySyncedEntities[] = $translatable;

        // Only synchronize TranslatableInterface
        if (!$translatable instanceof TranslatableInterface) {
            return;
        }

        $em = $args->getObjectManager();
        $properties = $em->getClassMetadata(\get_class($translatable))->getReflectionProperties();

        $sharedAmongstTranslationsProperties = array_filter($properties, function ($property) {
            return $this->isSharedAmongstTranslations($property);
        });

        // Update the translations if any property is to be shared
        if (empty($sharedAmongstTranslationsProperties)) {
            return;
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $em = $args->getObjectManager();
        $translations = $em
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
     * Determines if the property is to be shared amongst translations
     */
    protected function isSharedAmongstTranslations(\ReflectionProperty $property): bool
    {
        return [] !== $property->getAttributes(SharedAmongstTranslations::class, \ReflectionAttribute::IS_INSTANCEOF);
    }

    /**
     * Determines if the property is not a ManyToMany relation
     */
    protected function isNotManyToMany(\ReflectionProperty $property): bool
    {
        return [] === $property->getAttributes(ORM\Mapping\ManyToMany::class, \ReflectionAttribute::IS_INSTANCEOF);
    }
}
