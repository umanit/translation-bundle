<?php

namespace Umanit\TranslationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class TranslateEvent extends Event
{
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
     * TranslateEvent constructor.
     *
     * @param object $sourceEntity
     * @param object $translatedEntity
     */
    public function __construct($sourceEntity, $translatedEntity)
    {
        $this->sourceEntity     = $sourceEntity;
        $this->translatedEntity = $translatedEntity;
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
}
