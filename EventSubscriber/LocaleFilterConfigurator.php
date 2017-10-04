<?php

namespace Umanit\TranslationBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Umanit\TranslationBundle\Doctrine\Filter\LocaleFilter;

/**
 * Configure the LocaleFilter as it's not a service but has dependencies.
 *
 * @author Arthur Guigand <aguigand@umanit.fr>
 */
class LocaleFilterConfigurator implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $locale;

    /**
     * LocaleFilterConfigurator constructor.
     *
     * @param EntityManagerInterface $em
     * @param string                 $locale
     */
    public function __construct(EntityManagerInterface $em, $locale = 'en')
    {
        $this->locale = $locale;
        $this->em     = $em;
    }

    /**
     * @inheritdoc
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => [['onKernelRequest', 2]]];
    }

    /**
     * Called on each request.
     */
    public function onKernelRequest()
    {
        if ($this->em->getFilters()->has('umanit_translation_locale_filter')) {
            /** @var LocaleFilter $filter */
            $filter = $this->em->getFilters()->enable('umanit_translation_locale_filter');
            $filter->setLocale($this->locale);
        }
    }
}
