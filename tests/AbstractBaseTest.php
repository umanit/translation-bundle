<?php

namespace Umanit\TranslationBundle\Test;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Umanit\TranslationBundle\Translation\EntityTranslator;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
abstract class AbstractBaseTest extends KernelTestCase
{
    /** @var EntityTranslator */
    protected $translator;

    /** @var EntityManagerInterface */
    protected $em;

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
}
