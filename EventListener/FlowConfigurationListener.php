<?php

namespace Devhelp\FlowControlBundle\EventListener;


use Devhelp\FlowControlBundle\FlowConfiguration\Reader\ConfigurationReaderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * reads flow configuration for controller/action. saves it for latter usage
 */
class FlowConfigurationListener implements EventSubscriberInterface
{
    private $configurationReader;

    /**
     * @param ConfigurationReaderInterface $configurationReader - depending on configuration, different way
     * of reading flow can be chosen (yml/xml/annotation). Annotation is the only one supported
     * in current implementation
     */
    public function __construct(ConfigurationReaderInterface $configurationReader)
    {
        $this->configurationReader = $configurationReader;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $controller = $event->getController();

        if (!$this->configurationReader->supports($controller)) {
            return;
        }

        $configuration = $this->configurationReader->read($controller);

        $event->getRequest()->attributes->set('devhelp.flow_control.next_steps', $configuration['flow_steps']);
        $event->getRequest()->attributes->set('devhelp.flow_control.autocommit', $configuration['autocommit']);
    }

    public static function getSubscribedEvents()
    {
        return array(
            //must be run before FlowControlListener::onKernelController - the latter depends on this listener
            KernelEvents::CONTROLLER => array('onKernelController', 1000)
        );
    }
}
