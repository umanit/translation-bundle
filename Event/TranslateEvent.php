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
    const PRE_TRANSLATE = 'umanit_translation.pre_translate';

    /**
     * Event called after translation is done.
     */
    const POST_TRANSLATE = 'umanit_translation.post_translate';

    /**
     * The source entity being translated.
     *
     * @var object
     */
    protected $sourceEntity;

    /**
     * The translated entity.
     *
     * @var object
     */
    protected $translatedEntity;

    /**
     * The target locale
     *
     * @var string
     */
    private $locale;

    /**
     * TranslateEvent constructor.
     *
     * @param object $sourceEntity
     * @param object $translatedEntity
     * @param string $locale
     */
    public function __construct($sourceEntity, $translatedEntity, $locale)
    {
        $this->sourceEntity     = $sourceEntity;
        $this->translatedEntity = $translatedEntity;
        $this->locale           = $locale;
    }

    /**
     * @return object
     */
    public function getSourceEntity()
    {
        return $this->sourceEntity;
    }

    /**
     * @return object
     */
    public function getTranslatedEntity()
    {
        return $this->translatedEntity;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
