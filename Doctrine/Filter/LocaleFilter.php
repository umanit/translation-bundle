<?php

namespace Umanit\TranslationBundle\Doctrine\Filter;

use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Mapping\ClassMetadata;
use Umanit\TranslationBundle\Doctrine\Model\TranslatableInterface;

/**
 * Filters translatable contents by the current locale.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class LocaleFilter extends SQLFilter
{
    /**
     * @var string
     */
    protected $locale = 'en';

    /**
     * Dependency injection.
     *
     * @param string $locale
     */
    public function setLocale($locale = 'en')
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
        // If the entity is a TranslatableInterface
        if (in_array(TranslatableInterface::class, $targetEntity->getReflectionClass()->getInterfaceNames())) {
            return sprintf("%s.locale = '%s'", $targetTableAlias, $this->locale);
        }

        return '';
    }
}
