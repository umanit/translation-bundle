<?php

namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\ORM\EntityManagerInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;
use Umanit\TranslationBundle\Translation\Args\TranslationArgs;

class TranslatableEntityHandler implements TranslationHandlerInterface
{
    protected DoctrineObjectHandler $doctrineObjectHandler;
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, DoctrineObjectHandler $doctrineObjectHandler)
    {
        $this->em = $em;
        $this->doctrineObjectHandler = $doctrineObjectHandler;
    }

    public function supports(TranslationArgs $args): bool
    {
        return $args->getDataToBeTranslated() instanceof TranslatableInterface;
    }

    public function handleSharedAmongstTranslations(TranslationArgs $args)
    {
        return $this->translate($args);
    }

    public function handleEmptyOnTranslate(TranslationArgs $args)
    {
        return null;
    }

    public function translate(TranslationArgs $args)
    {
        $data = $args->getDataToBeTranslated();

        // Search in database if the content
        // exists, otherwise translate it.
        $existingTranslation = $this->em->getRepository(\get_class($data))->findOneBy([
            'locale' => $args->getTargetLocale(),
            'tuuid'  => $data->getTuuid(),
        ]);

        if (null !== $existingTranslation) {
            return $existingTranslation;
        }

        /** @var TranslatableInterface $clone */
        $clone = clone $args->getDataToBeTranslated();

        $this->doctrineObjectHandler->translateProperties(
            new TranslationArgs($clone, $clone->getLocale(), $args->getTargetLocale())
        );

        $clone->setLocale($args->getTargetLocale());

        return $clone;
    }
}
