<?php

namespace Umanit\TranslationBundle\Utils;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Umanit\TranslationBundle\Doctrine\Annotation\EmptyOnTranslate;
use Umanit\TranslationBundle\Doctrine\Annotation\SharedAmongstTranslations;

/**
 * Utils class used to shortcut annotation reader.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class AnnotationHelper
{
    /**
     * @var Reader
     */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Defines if the property is embedded.
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    public function isEmbedded(\ReflectionProperty $property): bool
    {
        return null !== $this->reader->getPropertyAnnotation($property, Embedded::class);
    }

    /**
     * Defines if the property is to be shared amongst parents' translations.
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    public function isSharedAmongstTranslations(\ReflectionProperty $property): bool
    {
        return null !== $this->reader->getPropertyAnnotation($property, SharedAmongstTranslations::class);
    }

    /**
     * Defines if the property should be emptied on translate.
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    public function isEmptyOnTranslate(\ReflectionProperty $property): bool
    {
        return null !== $this->reader->getPropertyAnnotation($property, EmptyOnTranslate::class);
    }

    /**
     * Defines if the property is a OneToOne.
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    public function isOneToOne(\ReflectionProperty $property): bool
    {
        return null !== $this->reader->getPropertyAnnotation($property, OneToOne::class);
    }

    /**
     * Defines if the property is an Id.
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    public function isId(\ReflectionProperty $property): bool
    {
        return null !== $this->reader->getPropertyAnnotation($property, Id::class);
    }

    /**
     * Defines if the property is a ManyToOne relation.
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    public function isManyToOne(\ReflectionProperty $property): bool
    {
        return null !== $this->reader->getPropertyAnnotation($property, ManyToOne::class);
    }

    /**
     * Defines if the property is a ManyToOne relation.
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    public function isOneToMany(\ReflectionProperty $property): bool
    {
        return null !== $this->reader->getPropertyAnnotation($property, OneToMany::class);
    }

    /**
     * Defines if the property is a ManyToMany relation.
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    public function isManyToMany(\ReflectionProperty $property): bool
    {
        return null !== $this->reader->getPropertyAnnotation($property, ManyToMany::class);
    }

    /**
     * Defines if the property can be null.
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    public function isNullable(\ReflectionProperty $property): bool
    {
        $columnAnnotation = $this->reader->getPropertyAnnotation($property, Column::class);

        return null === $columnAnnotation || true === $columnAnnotation->nullable;
    }
}
