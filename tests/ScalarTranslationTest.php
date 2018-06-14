<?php

namespace Umanit\TranslationBundle\Test;

use AppTestBundle\Entity\Scalar\Scalar;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;

/**
 * Test for scalar value.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class ScalarTranslationTest extends AbstractBaseTest
{
    /** @test */
    public function it_can_translate_scalar_value()
    {
        $entity      = $this->createEntity();
        $translation = $this->translator->translate($entity, 'fr');
        $this->em->flush();
        $this->assertAttributeContains('Test title', 'title', $translation);
        $this->assertIsTranslation($entity, $translation);
    }

    /** @test */
    public function it_can_share_scalar_value_amongst_translations()
    {
        $entity = $this->createEntity();
        /** @var Scalar $translation */
        $translation = $this->translator->translate($entity, 'fr');
        $this->em->persist($translation);
        $this->em->flush();
        // Update shared attribute
        $translation->setShared('Updated shared');
        $this->em->persist($translation);
        $this->em->flush();
        $this->assertAttributeContains('Updated shared', 'shared', $entity);
        $this->assertIsTranslation($entity, $translation);
    }

    /** @test */
    public function it_can_empty_scalar_value_on_translate()
    {
        $entity      = $this->createEntity();
        $translation = $this->translator->translate($entity, 'fr');

        $this->em->flush();
        $this->assertAttributeEmpty('empty', $translation);
        $this->assertIsTranslation($entity, $translation);
    }

    /**
     * Creates test entity.
     *
     * @return Scalar
     */
    protected function createEntity()
    {
        $entity =
            (new Scalar())
                ->setTitle('Test title')
                ->setShared('Shared attribute')
                ->setEmpty('Empty attribute')
        ;

        $this->em->persist($entity);

        return $entity;
    }

    /**
     * Assert a translation is actually a translation.
     *
     * @param TranslatableInterface $source
     * @param TranslatableInterface $translation
     */
    protected function assertIsTranslation(TranslatableInterface $source, TranslatableInterface $translation)
    {
        $this->assertAttributeContains('fr', 'locale', $translation);
        $this->assertAttributeContains($source->getTuuid(), 'tuuid', $translation);
        $this->assertNotEquals(spl_object_hash($source), spl_object_hash($translation));
    }
}
