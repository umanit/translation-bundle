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

    public function supports($data, \ReflectionProperty $property = null): bool
    {
        return $data instanceof TranslatableInterface;
    }

    public function handleSharedAmongstTranslations($data, string $locale)
    {
        // Search in database if the content
        // exists, otherwise translate it.
        $existingTranslation = $this->em->getRepository(\get_class($data))->findOneBy([
            'locale' => $locale,
            'uuid'   => $data->getUuid(),
        ]);

        if (null !== $existingTranslation) {
            return $existingTranslation;
        }

        return $this->translate($data, $locale);
    }

    public function handleEmptyOnTranslate($data, string $locale)
    {
        return null;
    }

    public function translate($data, string $locale, \ReflectionProperty $property = null, $parent = null)
    {
        /** @var TranslatableInterface $clone */
        $clone = clone $data;

        $this->doctrineObjectHandler->translateProperties($clone, $locale, $clone);

        $clone->setLocale($locale);

        $this->em->persist($clone);

        return $clone;
    }

}
