<?php

namespace Devhelp\FlowControlBundle\EventListener;


use Devhelp\FlowControlBundle\Exception\NoValidStepsFoundException;
use Devhelp\FlowControlBundle\CurrentSteps\CurrentStepsInterface;
use Devhelp\FlowControl\FlowControl;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * controls the flow using flow configuration from request
 * @see FlowConfigurationListener
 */
class FlowControlListener implements EventSubscriberInterface
{
    /**
     * @var FlowControl
     */
    protected $flowControl;

    /**
     * @var CurrentStepsInterface
     */
    protected $currentSteps;

    public function __construct(FlowControl $flowControl, CurrentStepsInterface $currentSteps)
    {
        $this->flowControl = $flowControl;
        $this->currentSteps = $currentSteps;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $nextSteps = $event->getRequest()->attributes->get('devhelp.flow_control.next_steps');

        if (!count($nextSteps)) {
            return;
        }

        $currentSteps = $this->currentSteps->get(array_keys($nextSteps));

        if (!is_array($currentSteps)) {
            throw new \InvalidArgumentException("array expected");
        }

        $this->flowControl->setFlowSteps($currentSteps);

        $validSteps = $this->flowControl->resolveValid($nextSteps);

        if (!count($validSteps)) {
            throw new NoValidStepsFoundException($nextSteps);
        } else {
            $event->getRequest()->attributes->set('devhelp.flow_control.valid_steps', $validSteps);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onKernelController')
        );
    }
}
