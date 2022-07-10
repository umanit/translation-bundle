<?php

namespace Umanit\TranslationBundle\Doctrine\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;

/**
 * Filters translatable contents by the current locale.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class LocaleFilter extends SQLFilter
{
    protected ?string $locale;

    /**
     * Dependency injection.
     */
    public function setLocale(?string $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @inheritdoc.
     *
     * @param ClassMetadata $targetEntity
     * @param string        $targetTableAlias
     *
     * @return string
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (null === $this->locale) {
            return '';
        }

        // If the entity is a TranslatableInterface
        if (\in_array(TranslatableInterface::class, $targetEntity->getReflectionClass()->getInterfaceNames(), true)) {
            return sprintf("%s.locale = '%s'", $targetTableAlias, $this->locale);
        }

        return '';
    }
}
