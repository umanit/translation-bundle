<?php

namespace Umanit\TranslationBundle\Utils;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\Embedded;
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
    public function isEmbedded(\ReflectionProperty $property)
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
    public function isSharedAmongstTranslations(\ReflectionProperty $property)
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
    public function isEmptyOnTranslate(\ReflectionProperty $property)
    {
        return null !== $this->reader->getPropertyAnnotation($property, EmptyOnTranslate::class);
    }

}
