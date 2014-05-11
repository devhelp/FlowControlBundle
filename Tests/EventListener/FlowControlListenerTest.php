<?php

namespace Devhelp\FlowControlBundle\Tests\EventListener;


use Devhelp\FlowControlBundle\EventListener\FlowControlListener;
use Devhelp\FlowControlBundle\Exception\NoValidStepsFoundException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;

class FlowControlListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function subscribesKernelController()
    {
        $expected = array(
            KernelEvents::CONTROLLER => array('onKernelController')
        );

        $listener = new FlowControlListener(
            $this->getFlowControlMock(),
            $this->getCurrentStepsMock()
        );

        $this->assertSame($expected, $listener->getSubscribedEvents());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function throwsExceptionIfCurrentFlowStepsAreNotAnArray()
    {
        $listener = new FlowControlListener(
            $this->getFlowControlMock(),
            $this->getCurrentStepsMock()
        );

        $request = new Request();
        $request->attributes->set('devhelp.flow_control.next_steps', array('some_value'));

        $event = $this->getFilterControllerEventMock(true, $request);

        $listener->onKernelController($event);
    }

    /**
     * @test
     * @expectedException Devhelp\FlowControlBundle\Exception\NoValidStepsFoundException
     */
    public function throwsExceptionIfNoValidStepsWereResolved()
    {
        $listener = new FlowControlListener(
            $this->getFlowControlMock(array()),
            $this->getCurrentStepsMock(array())
        );

        $request = new Request();
        $request->attributes->set('devhelp.flow_control.next_steps', array('some_value'));

        $event = $this->getFilterControllerEventMock(true, $request);

        $listener->onKernelController($event);
    }

    /**
     * @test
     */
    public function doesNotAssignValidStepsToTheRequestIfItIsNotMasterRequest()
    {
        $listener = new FlowControlListener(
            $this->getFlowControlMock(),
            $this->getCurrentStepsMock()
        );

        $request = new Request();
        $request->attributes->set('devhelp.flow_control.next_steps', array('some_value'));

        $event = $this->getFilterControllerEventMock(false, $request);

        $listener->onKernelController($event);

        $this->assertNull($request->attributes->get('devhelp.flow_control.valid_steps'));
    }

    /**
     * @test
     */
    public function doesNotAssignValidStepsToTheRequestIfThereAreNotNextSteps()
    {
        $listener = new FlowControlListener(
            $this->getFlowControlMock(),
            $this->getCurrentStepsMock()
        );

        $request = new Request();
        $request->attributes->set('devhelp.flow_control.next_steps', array());

        $event = $this->getFilterControllerEventMock(true, $request);

        $listener->onKernelController($event);

        $this->assertNull($request->attributes->get('devhelp.flow_control.valid_steps'));
    }

    /**
     * @test
     */
    public function assignsValidStepsToTheRequest()
    {
        $expectedValidSteps = array('example_flow' => 'example_step');

        $listener = new FlowControlListener(
            $this->getFlowControlMock($expectedValidSteps),
            $this->getCurrentStepsMock(array())
        );

        $request = new Request();
        $request->attributes->set('devhelp.flow_control.next_steps', array('some_value'));

        $event = $this->getFilterControllerEventMock(true, $request);

        $listener->onKernelController($event);

        $this->assertSame($expectedValidSteps, $request->attributes->get('devhelp.flow_control.valid_steps'));
    }

    private function getFilterControllerEventMock($isMasterRequest = false, $request = null)
    {
        $mock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterControllerEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('isMasterRequest')
            ->will($this->returnValue($isMasterRequest));

        $mock
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        return $mock;
    }

    private function getFlowControlMock($resolvedSteps = null)
    {
        $mock = $this->getMockBuilder('Devhelp\FlowControl\FlowControl')
                     ->disableOriginalConstructor()
                     ->getMock();

        $mock
            ->expects($this->any())
            ->method('resolveValid')
            ->will($this->returnValue($resolvedSteps));

        return $mock;
    }

    private function getCurrentStepsMock($flowSteps = null)
    {
        $mock = $this->getMockBuilder('Devhelp\FlowControlBundle\CurrentSteps\CurrentStepsInterface')
                     ->disableOriginalConstructor()
                     ->getMock();

        $mock
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($flowSteps));

        return $mock;
    }
}
