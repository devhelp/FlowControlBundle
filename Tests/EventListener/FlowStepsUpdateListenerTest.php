<?php

namespace Devhelp\FlowControlBundle\Tests\EventListener;


use Devhelp\FlowControlBundle\EventListener\FlowStepsUpdateListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;

class FlowStepsUpdateListenerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function subscribesKernelTerminateEvents()
    {
        $expected = array(
            KernelEvents::TERMINATE => array('onKernelTerminate')
        );

        $listener = new FlowStepsUpdateListener($this->getCurrentStepsMock());

        $this->assertSame($expected, $listener->getSubscribedEvents());
    }

    /**
     * @test
     */
    public function doesNotUpdateStepsIfResponseIsNotSuccessfulAndIsNotRedirection()
    {
        $listener = new FlowStepsUpdateListener($this->getCurrentStepsMock());

        $event = $this->getPostResponseEventMock(false, false);

        $listener->onKernelTerminate($event);
    }

    /**
     * @test
     * @dataProvider providerRequestData
     */
    public function doesNotUpdateStepsIfRequestDoesNotContainRequiredData($validSteps, $autocommit)
    {
        $listener = new FlowStepsUpdateListener($this->getCurrentStepsMock());

        $request = new Request();
        $request->attributes->set('devhelp.flow_control.valid_steps', $validSteps);
        $request->attributes->set('devhelp.flow_control.autocommit', $autocommit);

        $event = $this->getPostResponseEventMock(true, true, $request);

        $listener->onKernelTerminate($event);
    }

    /**
     * @test
     */
    public function updatesCurrentSteps()
    {
        $listener = new FlowStepsUpdateListener($this->getCurrentStepsMock(true));

        $request = new Request();
        $request->attributes->set('devhelp.flow_control.valid_steps', array('example_flow' => 'example_step'));
        $request->attributes->set('devhelp.flow_control.autocommit', true);

        $event = $this->getPostResponseEventMock(true, true, $request);

        $listener->onKernelTerminate($event);
    }

    public function providerRequestData()
    {
        return array(
            array(array(), true),
            array(array('example_flow' => 'example_step'), false),
            array(array(), false),
        );
    }

    private function getCurrentStepsMock($updates = false)
    {
        $mock = $this->getMockBuilder('Devhelp\FlowControlBundle\CurrentSteps\CurrentStepsInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->exactly((int) $updates))
            ->method('update');

        return $mock;
    }

    private function getPostResponseEventMock($isSuccessful, $isRedirection, $request = null)
    {
        $mock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\PostResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $responseMock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')
            ->disableOriginalConstructor()
            ->getMock();

        $responseMock
            ->expects($this->any())
            ->method('isSuccessful')
            ->will($this->returnValue($isSuccessful));

        $responseMock
            ->expects($this->any())
            ->method('isRedirection')
            ->will($this->returnValue($isRedirection));

        $mock
            ->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($responseMock));

        $mock
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        return $mock;
    }
}
