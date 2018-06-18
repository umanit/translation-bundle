<?php

namespace Umanit\TranslationBundle\Test;

use AppTestBundle\Entity\Embedded;

/**
 * Tests for embedded entities.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class EmbeddedTranslationTest extends AbstractBaseTest
{
    const TARGET_LOCALE = 'fr';

    /** @test */
    public function it_can_translate_embedded_entity()
    {
        $address = (new Embedded\Address())
            ->setStreet('13 place Sophie Trébuchet')
            ->setCity('Nantes')
            ->setPostalCode('44000')
            ->setCountry('France')
        ;
        $entity  = (new Embedded\Translatable())
            ->setAddress($address)
            ->setLocale('en')
        ;

        $this->em->persist($entity);

        $trans = $this->translator->translate($entity, self::TARGET_LOCALE);

        $this->em->persist($trans);

        $this->em->flush();

        $this->assertEquals($entity->getAddress(), $trans->getAddress());
    }

    /** @test */
    public function it_can_empty_embedded_entity()
    {
        $address = (new Embedded\Address())
            ->setStreet('13 place Sophie Trébuchet')
            ->setCity('Nantes')
            ->setPostalCode('44000')
            ->setCountry('France')
        ;
        $entity  = (new Embedded\Translatable())
            ->setEmptyAddress($address)
            ->setLocale('en')
        ;

        $this->em->persist($entity);

        /** @var Embedded\Translatable $trans */
        $trans = $this->translator->translate($entity, self::TARGET_LOCALE);

        $this->em->persist($trans);

        $this->em->flush();

        $this->assertEquals($entity->getEmptyAddress(), $address);
        $this->assertEmpty($trans->getEmptyAddress());
    }
}
