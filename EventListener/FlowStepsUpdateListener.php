<?php

namespace Devhelp\FlowControlBundle\EventListener;


use Devhelp\FlowControlBundle\CurrentSteps\CurrentStepsInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * updates flow steps on successful (non-redirect) response
 * @see FlowControlListener
 */
class FlowStepsUpdateListener implements EventSubscriberInterface
{
    /**
     * @var CurrentStepsInterface
     */
    protected $currentSteps;

    public function __construct(CurrentStepsInterface $currentSteps)
    {
        $this->currentSteps = $currentSteps;
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        if (!$event->getResponse()->isSuccessful() && !$event->getResponse()->isRedirection()) {
            return;
        }

        $autocommit = $event->getRequest()->attributes->get('devhelp.flow_control.autocommit');
        $validSteps = $event->getRequest()->attributes->get('devhelp.flow_control.valid_steps');

        if (!$validSteps || !$autocommit) {
            return;
        }

        $this->currentSteps->update($validSteps);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::TERMINATE => array('onKernelTerminate')
        );
    }
}
