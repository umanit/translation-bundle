<?php

namespace Umanit\TranslationBundle\Test;

use AppTestBundle\Entity\Translatable\TranslatableOneToManyBidirectionalChild;
use AppTestBundle\Entity\Translatable\TranslatableOneToManyBidirectionalParent;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class TranslatableOneToManyBidirectionalTest extends AbstractBaseTest
{
    const TARGET_LOCALE = 'fr';

    /** @test */
    public function it_can_translate_bidirectional_one_to_many()
    {
        $children = new ArrayCollection([
            new TranslatableOneToManyBidirectionalChild(),
            new TranslatableOneToManyBidirectionalChild(),
            new TranslatableOneToManyBidirectionalChild(),
        ]);

        $parent = (new TranslatableOneToManyBidirectionalParent())->setChildren($children);
        $this->em->persist($parent);
        /** @var TranslatableOneToManyBidirectionalParent $parentTranslation */
        $parentTranslation = $this->translator->translate($parent, self::TARGET_LOCALE);
        $this->em->persist($parentTranslation);

        $this->em->flush();

        $this->assertEquals(self::TARGET_LOCALE, $parentTranslation->getChildren()->first()->getLocale());
        $this->assertEquals($parent->getChildren()->first()->getTuuid(), $parentTranslation->getChildren()->first()->getTuuid());
    }
}
