<?php

namespace Umanit\TranslationBundle\Test;

use AppTestBundle\Entity\Scalar\ScalarTestEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Translation\EntityTranslator;

/**
 * Test for scalar value.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class ScalarTranslationTest extends KernelTestCase
{
    /** @var EntityTranslator */
    private $translator;

    /** @var EntityManagerInterface */
    private $em;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->translator =
            $kernel
                ->getContainer()
                ->get('umanit_translation.translation.entity_translator')
        ;

        $this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

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

        $translation = $this->translator->translate($entity, 'fr');

        $this->em->flush();
        $this->assertAttributeContains('Shared attribute', 'shared', $translation);
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
     * @return ScalarTestEntity
     */
    protected function createEntity()
    {
        $entity =
            (new ScalarTestEntity())
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
        $this->assertAttributeContains($source->getUuid(), 'uuid', $translation);
        $this->assertNotEquals(spl_object_hash($source), spl_object_hash($translation));
    }
}
