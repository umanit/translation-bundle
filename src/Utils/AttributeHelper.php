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
use Umanit\TranslationBundle\Doctrine\Attribute\EmptyOnTranslate;
use Umanit\TranslationBundle\Doctrine\Attribute\SharedAmongstTranslations;

/**
 * Utils class used to shortcut annotation reader
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class AttributeHelper
{
    /**
     * Defines if the property is embedded
     */
    public function isEmbedded(\ReflectionProperty $property): bool
    {
        return [] !== $property->getAttributes(Embedded::class, \ReflectionAttribute::IS_INSTANCEOF);
    }

    /**
     * Defines if the property is to be shared amongst parents' translations
     */
    public function isSharedAmongstTranslations(\ReflectionProperty $property): bool
    {
        return [] !== $property->getAttributes(SharedAmongstTranslations::class, \ReflectionAttribute::IS_INSTANCEOF);
    }

    /**
     * Defines if the property should be emptied on translate
     */
    public function isEmptyOnTranslate(\ReflectionProperty $property): bool
    {
        return [] !== $property->getAttributes(EmptyOnTranslate::class, \ReflectionAttribute::IS_INSTANCEOF);
    }

    /**
     * Defines if the property is a OneToOne relation
     *
     * @param \ReflectionProperty $property
     *
     * @return bool
     */
    public function isOneToOne(\ReflectionProperty $property): bool
    {
        return [] !== $property->getAttributes(OneToOne::class, \ReflectionAttribute::IS_INSTANCEOF);
    }

    /**
     * Defines if the property is an ID
     */
    public function isId(\ReflectionProperty $property): bool
    {
        return [] !== $property->getAttributes(Id::class, \ReflectionAttribute::IS_INSTANCEOF);
    }

    /**
     * Defines if the property is a ManyToOne relation
     */
    public function isManyToOne(\ReflectionProperty $property): bool
    {
        return [] !== $property->getAttributes(ManyToOne::class, \ReflectionAttribute::IS_INSTANCEOF);
    }

    /**
     * Defines if the property is a ManyToOne relation
     */
    public function isOneToMany(\ReflectionProperty $property): bool
    {
        return [] !== $property->getAttributes(OneToMany::class, \ReflectionAttribute::IS_INSTANCEOF);
    }

    /**
     * Defines if the property is a ManyToMany relation
     */
    public function isManyToMany(\ReflectionProperty $property): bool
    {
        return [] !== $property->getAttributes(ManyToMany::class, \ReflectionAttribute::IS_INSTANCEOF);
    }

    /**
     * Defines if the property can be null.
     */
    public function isNullable(\ReflectionProperty $property): bool
    {
        $ra = $property->getAttributes(Column::class, \ReflectionAttribute::IS_INSTANCEOF);

        if (0 < count($ra)) {
            $ra = reset($ra);
            $args = $ra->getArguments();

            if (array_key_exists('nullable', $args) && true === $args['nullable']) {
                return true;
            }

            return false;
        }

        return true;
    }
}
