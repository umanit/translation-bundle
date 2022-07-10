<?php

namespace Umanit\TranslationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class TranslateEvent extends Event
{
    /**
     * Event called before translation is done.
     */
    public const PRE_TRANSLATE = 'umanit_translation.pre_translate';

    /**
     * Event called after translation is done.
     */
    public const POST_TRANSLATE = 'umanit_translation.post_translate';

    /**
     * The source entity being translated.
     */
    protected object $sourceEntity;

    /**
     * The translated entity.
     */
    protected ?object $translatedEntity;

    /**
     * The target locale
     */
    private string $locale;

    public function __construct(object $sourceEntity, string $locale, object $translatedEntity = null)
    {
        $this->sourceEntity = $sourceEntity;
        $this->locale = $locale;
        $this->translatedEntity = $translatedEntity;
    }

    public function getSourceEntity(): object
    {
        return $this->sourceEntity;
    }

    public function getTranslatedEntity(): ?object
    {
        return $this->translatedEntity;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
