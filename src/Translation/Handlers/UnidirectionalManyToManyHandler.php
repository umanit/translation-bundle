<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use Umanit\TranslationBundle\Translation\Args\TranslationArgs;
use Umanit\TranslationBundle\Translation\EntityTranslator;
use Umanit\TranslationBundle\Utils\AnnotationHelper;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class UnidirectionalManyToManyHandler implements TranslationHandlerInterface
{
    /**
     * @var AnnotationHelper
     */
    private $annotationHelper;

    /**
     * @var Reader
     */
    private $reader;
    /**
     * @var EntityTranslator
     */
    private $translator;
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        AnnotationHelper $annotationHelper,
        Reader $reader,
        EntityTranslator $translator,
        EntityManagerInterface $em
    ) {
        $this->annotationHelper = $annotationHelper;
        $this->reader           = $reader;
        $this->translator       = $translator;
        $this->em               = $em;
    }

    /**
     * {@inheritdoc}
     *
     * @param TranslationArgs $args
     *
     * @return bool
     */
    public function supports(TranslationArgs $args): bool
    {
        if (!$args->getDataToBeTranslated() instanceof Collection) {
            return false;
        }

        if (!$this->annotationHelper->isManyToMany($args->getProperty())) {
            return false;
        }

        $propAnnotations = $this->reader->getPropertyAnnotations($args->getProperty());

        foreach ($propAnnotations as $propAnnotation) {
            if (property_exists($propAnnotation, 'mappedBy') && null !== $propAnnotation->mappedBy) {
                return false;
            }
            if (property_exists($propAnnotation, 'inversedBy') && null !== $propAnnotation->inversedBy) {
                return false;
            }
        }

        return true;
    }

    public function handleSharedAmongstTranslations(TranslationArgs $args)
    {
        return $this->translate($args);
    }

    public function handleEmptyOnTranslate(TranslationArgs $args)
    {
        return new ArrayCollection();
    }

    public function translate(TranslationArgs $args)
    {
        $newOwner = $args->getTranslatedParent();
        // Get the owner's fieldName
        $associations = $this->em->getClassMetadata(\get_class($newOwner))->getAssociationMappings();
        $association  = $associations[$args->getProperty()->name];
        $fieldName    = $association['fieldName'];

        $reflection = new \ReflectionProperty(\get_class($newOwner), $fieldName);
        $reflection->setAccessible(true);
        /** @var PersistentCollection $collection */
        $collection = $reflection->getValue($newOwner);

        foreach ($collection as $key => $item) {
            $collection->remove($key);
        }

        foreach ($args->getDataToBeTranslated() as $key => $itemtoBeTrans) {
            $itemTrans = $this->translator->translate($itemtoBeTrans, $args->getTargetLocale());
            if (!$collection->contains($itemTrans)) {
                $collection->add($itemTrans);
            }
        }

        return $collection;
    }

}
