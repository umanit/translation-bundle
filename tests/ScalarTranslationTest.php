<?php

namespace Umanit\TranslationBundle\Test;

use AppTestBundle\Entity\Scalar\ScalarTestEntity;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
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
        $entity->setTitle('Test title');

        $translation = $this->translator->translate($entity, 'fr');

        $this->assertAttributeContains('Test title', 'title', $translation);
    }
}
