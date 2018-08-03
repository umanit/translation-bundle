<?php

namespace Umanit\TranslationBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
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
     * @var array
     */
    private $disabledFirewalls;

    /**
     * @var FirewallMap
     */
    private $firewallMap;

    /**
     * LocaleFilterConfigurator constructor.
     *
     * @param EntityManagerInterface $em
     * @param FirewallMap            $firewallMap
     * @param array                  $disabledFirewalls
     */
    public function __construct(EntityManagerInterface $em, FirewallMap $firewallMap, array $disabledFirewalls)
    {
        $this->em                = $em;
        $this->disabledFirewalls = $disabledFirewalls;
        $this->firewallMap = $firewallMap;
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
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($this->em->getFilters()->has('umanit_translation_locale_filter')) {
            if ($this->em->getFilters()->isEnabled('umanit_translation_locale_filter') && $this->isDisabledFirewall($event->getRequest())) {
                $this->em->getFilters()->disable('umanit_translation_locale_filter');
                return;
            }
            /** @var LocaleFilter $filter */
            $filter = $this->em->getFilters()->enable('umanit_translation_locale_filter');
            $filter->setLocale($event->getRequest()->getLocale());
        }
    }

    /**
     * Indicates if the current firewall should disable the filter.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isDisabledFirewall(Request $request)
    {
        if (null === $this->firewallMap->getFirewallConfig($request)) {
            return false;
        }

        return \in_array($this->firewallMap->getFirewallConfig($request)->getName(), $this->disabledFirewalls, true);
    }
}
