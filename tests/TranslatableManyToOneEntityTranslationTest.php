<?php

namespace Umanit\TranslationBundle\Test;

use AppTestBundle\Entity\Scalar\Scalar;
use AppTestBundle\Entity\Translatable\TranslatableManyToOne;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class TranslatableManyToOneEntityTranslationTest extends AbstractBaseTest
{
    const TARGET_LOCALE = 'fr';

    /** @test */
    public function it_can_translate_simple_value()
    {
        $associatedEntity = (new Scalar())->setTitle('simple');

        $entity =
            (new TranslatableManyToOne())
                ->setSimple($associatedEntity);

        $this->em->persist($entity);

        /** @var TranslatableManyToOne $translation */
        $translation = $this->translator->translate($entity, self::TARGET_LOCALE);

        $this->em->persist($translation);
        $this->em->flush();
        $this->assertNotEquals($associatedEntity, $translation->getSimple());
        $this->assertAttributeContains(self::TARGET_LOCALE, 'locale', $translation->getSimple());
        $this->assertIsTranslation($entity, $translation);
    }


    /** @test */
    public function it_must_associate_existing_translation()
    {
        $associatedEntity = (new Scalar())->setTitle('simple');
        $this->em->persist($associatedEntity);

        $translatedAssociatedEntity = $this->translator->translate($associatedEntity, self::TARGET_LOCALE);
        $this->em->persist($translatedAssociatedEntity);

        $entity =
            (new TranslatableManyToOne())
                ->setSimple($associatedEntity);

        $this->em->persist($entity);
        $this->em->flush();

        /** @var TranslatableManyToOne $translation */
        $translation = $this->translator->translate($entity, self::TARGET_LOCALE);

        $this->em->persist($translation);
        $this->em->flush();
        $this->assertNotEquals($associatedEntity, $translation->getSimple());
        $this->assertEquals($translatedAssociatedEntity, $translation->getSimple());
        $this->assertAttributeContains(self::TARGET_LOCALE, 'locale', $translation->getSimple());
        $this->assertIsTranslation($entity, $translation);
    }

    /** @test */
    public function it_can_share_translatable_entity_value_amongst_translations()
    {
        $associatedEntity = (new Scalar())->setTitle('shared');

        $this->em->persist($associatedEntity);
        $this->em->flush();

        // Pre-set the translation to confirm that it'll
        // be picked up by the parent's translation.
        $translationAssociatedEntity = $this->translator->translate($associatedEntity, self::TARGET_LOCALE);

        $this->em->persist($translationAssociatedEntity);
        $this->em->flush();

        $entity =
            (new TranslatableManyToOne())
                ->setShared($associatedEntity);

        $this->em->persist($entity);

        /** @var TranslatableManyToOne $translation */
        $translation = $this->translator->translate($entity, self::TARGET_LOCALE);

        $this->em->persist($translation);

        $this->em->flush();
        $this->assertEquals($translationAssociatedEntity, $translation->getShared());
        $this->assertIsTranslation($entity, $translation);
    }

    /** @test */
    public function it_can_empty_translatable_entity_value()
    {
        $associatedEntity = (new Scalar())->setTitle('empty');

        $entity =
            (new TranslatableManyToOne())
                ->setEmpty($associatedEntity);

        $this->em->persist($entity);

        /** @var TranslatableManyToOne $translation */
        $translation = $this->translator->translate($entity, self::TARGET_LOCALE);

        $this->em->persist($translation);
        $this->em->flush();

        $this->assertEquals(null, $translation->getEmpty());
        $this->assertIsTranslation($entity, $translation);
    }

    /**
     * Assert a translation is actually a translation.
     *
     * @param TranslatableInterface $source
     * @param TranslatableInterface $translation
     */
    protected function assertIsTranslation(TranslatableInterface $source, TranslatableInterface $translation)
    {
        $this->assertAttributeContains(self::TARGET_LOCALE, 'locale', $translation);
        $this->assertAttributeContains($source->getTuuid(), 'tuuid', $translation);
        $this->assertNotEquals(spl_object_hash($source), spl_object_hash($translation));
    }
}
