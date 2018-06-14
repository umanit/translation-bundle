<?php

namespace Umanit\TranslationBundle\Test;

use AppTestBundle\Entity\Translatable\ManyToManyBidirectionalChild;
use AppTestBundle\Entity\Translatable\TranslatableManyToManyBidirectionalChild;
use AppTestBundle\Entity\Translatable\TranslatableManyToManyBidirectionalParent;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class TranslatableManyToManyTranslationTest extends AbstractBaseTest
{
    const TARGET_LOCALE = 'fr';

    /** @test */
    public function it_can_translate_many_to_many()
    {
        // Create 3 children entities
        $child1 = (new TranslatableManyToManyBidirectionalChild())->setLocale('en');
        $child2 = (new TranslatableManyToManyBidirectionalChild())->setLocale('en');
        $child3 = (new TranslatableManyToManyBidirectionalChild())->setLocale('en');

        $this->em->persist($child1);
        $this->em->persist($child2);
        $this->em->persist($child3);

        // Create 1 parent entity
        $parent = (new TranslatableManyToManyBidirectionalParent())->setLocale('en');
        $parent
            ->addSimpleChild($child1)
            ->addSimpleChild($child2)
            ->addSimpleChild($child3)
        ;
        $this->em->persist($parent);

        // Translate the parent
        /** @var TranslatableManyToManyBidirectionalParent $parentTranslation */
        $parentTranslation = $this->translator->translate($parent, self::TARGET_LOCALE);
        $this->em->persist($parentTranslation);
        $this->em->flush();

        // Make sure the children of the translated parent are
        // translated and their parent is $translatedParent
        foreach ($parentTranslation->getSimpleChildren() as $child) {
            /** @var TranslatableManyToManyBidirectionalChild $child */
            $this->assertEquals($child->getSimpleParents()->first(), $parentTranslation);
        }

        // Make sure the parent of the original children didn't change.
        foreach ($parentTranslation->getSimpleChildren() as $child) {
            /** @var TranslatableManyToManyBidirectionalChild $child */
            $this->assertEquals($child->getSimpleParents()->first(), $parentTranslation);
        }
    }

    /** @test */
    public function it_can_empty_on_translate()
    {
        // Create 3 children entities
        $child1 = (new TranslatableManyToManyBidirectionalChild())->setLocale('en');
        $child2 = (new TranslatableManyToManyBidirectionalChild())->setLocale('en');
        $child3 = (new TranslatableManyToManyBidirectionalChild())->setLocale('en');

        $this->em->persist($child1);
        $this->em->persist($child2);
        $this->em->persist($child3);
        // Create 1 parent entity
        $parent = (new TranslatableManyToManyBidirectionalParent())->setLocale('en');
        $parent
            ->addEmptyChild($child1)
            ->addEmptyChild($child2)
            ->addEmptyChild($child3)
        ;
        $this->em->persist($parent);
        // Translate the parent
        /** @var TranslatableManyToManyBidirectionalParent $parentTranslation */
        $parentTranslation = $this->translator->translate($parent, self::TARGET_LOCALE);
        $this->em->persist($parentTranslation);
        $this->em->flush();

        // Assert that the translated parents has an empty list of child
        $this->assertEmpty($parentTranslation->getSimpleChildren());
    }


    /** @test */
    public function it_can_share_many_to_many()
    {
        // Create 3 children entities
        $child1 = new ManyToManyBidirectionalChild();

        $this->em->persist($child1);

        // Create 1 parent entity
        $parent = (new TranslatableManyToManyBidirectionalParent())->setLocale('en');
        $parent->addSharedChild($child1);
        $this->em->persist($parent);
        $this->em->flush();

        $parentTranslation = $this->translator->translate($parent, self::TARGET_LOCALE);
        $this->em->persist($parentTranslation);
        $this->em->flush();

        $this->assertGreaterThan(0, $parent->getSharedChildren()->count());
        $this->assertGreaterThan(0, $parentTranslation->getSharedChildren()->count());
        $this->assertEquals($parent->getSharedChildren()->count(), $parentTranslation->getSharedChildren()->count());
        $this->assertNotEquals($parent->getSharedChildren()->first(), $parentTranslation->getSharedChildren()->first());

        $this->assertEquals($parent->getSharedChildren()->first()->getSharedParents()->first(), $parent);
        $this->assertEquals($parentTranslation->getSharedChildren()->first()->getSharedParents()->first(), $parentTranslation);
    }
}
