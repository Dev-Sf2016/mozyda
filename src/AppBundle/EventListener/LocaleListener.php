<?php
// src/AppBundle/EventListener/LocaleListener.php
namespace AppBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LocaleListener implements EventSubscriberInterface
{

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    private $defaultLocale;

    /**
     * Constructor.
     *
     * @param string|null $defaultLocale
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct($defaultLocale = 'en', UrlGeneratorInterface $urlGenerator)
    {
        $this->defaultLocale = $defaultLocale;
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }
        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        } else {
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
            $response = new RedirectResponse($this->urlGenerator->generate('homepage', array('_locale' => $request->getLocale())));
            $event->setResponse($response);
        }
    }


    public static function getSubscribedEvents()
    {
        return array(
            // must be registered after the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 15)),
        );
    }
}

?>