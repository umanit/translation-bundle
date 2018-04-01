<?php


namespace Umanit\TranslationBundle\Translation\Handlers;

use Doctrine\ORM\EntityManagerInterface;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class TranslatableEntityHandler implements TranslationHandlerInterface
{
    /**
     * @var DoctrineObjectHandler
     */
    private $doctrineObjectHandler;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function supports($data): bool
    {
        return $data instanceof TranslatableInterface;
    }

    /**
     * TranslatableEntity constructor.
     *
     * @param EntityManagerInterface $em
     * @param DoctrineObjectHandler  $doctrineObjectHandler
     */
    public function __construct(EntityManagerInterface $em, DoctrineObjectHandler $doctrineObjectHandler)
    {
        $this->em                    = $em;
        $this->doctrineObjectHandler = $doctrineObjectHandler;
    }

    public function handleSharedAmongstTranslations($data)
    {
        // TODO: Implement handleSharedAmongstTranslations() method.

        return null;
    }

    public function handleEmptyOnTranslate($data)
    {
        // TODO: Implement handleEmptyOnTranslate() method.
        return null;
    }

    public function translate($data, string $locale)
    {
        /** @var TranslatableInterface $clone */
        $clone = clone $data;

        $this->doctrineObjectHandler->translateProperties($clone, $locale);

        $clone->setLocale($locale);

        $this->em->persist($clone);

        return $clone;
    }

}
