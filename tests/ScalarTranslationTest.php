<?php

namespace Umanit\TranslationBundle\Test;

use AppTestBundle\Entity\Scalar\ScalarTestEntity;
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
    }

    /** @test */
    public function it_can_translate_scalar_value()
    {
        $entity = new ScalarTestEntity('en');
        $entity
            ->setTitle('Test title')
            ->setShared('Shared attribute')
        ;

        $translation = $this->translator->translate($entity, 'fr');

        $this->assertAttributeContains('Test title', 'title', $translation);
        $this->assertIsTranslation($entity, $translation);
    }

    /** @test */
    public function it_can_share_scalar_value_amongst_translations()
    {
        $entity = new ScalarTestEntity('en');
        $entity
            ->setTitle('Test title')
            ->setShared('Shared attribute')
        ;

        $translation = $this->translator->translate($entity, 'fr');

        $this->assertAttributeContains('Shared attribute', 'shared', $translation);
        $this->assertIsTranslation($entity, $translation);
    }

    /** @test */
    public function it_can_empty_scalar_value_on_translate()
    {
        $entity = new ScalarTestEntity('en');
        $entity
            ->setTitle('Test title')
            ->setShared('Shared attribute')
            ->setEmpty('Empty attribute')
        ;

        $translation = $this->translator->translate($entity, 'fr');

        $this->assertAttributeEmpty('empty', $translation);
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
        $this->assertAttributeContains('fr', 'locale', $translation);
        $this->assertAttributeContains($source->getUuid(), 'uuid', $translation);
        $this->assertNotEquals(spl_object_hash($source), spl_object_hash($translation));
    }
}
