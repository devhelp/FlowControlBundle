<?php

namespace Devhelp\FlowControlBundle\Tests\EventListener;


use Devhelp\FlowControlBundle\EventListener\FlowConfigurationListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;

class FlowConfigurationListenerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function doesNothingIfRequestIsNotMasterRequest()
    {
        $reader = $this->getConfigurationReader(true);

        $listener = new FlowConfigurationListener($reader);

        $event = $this->getEventMock();

        $listener->onKernelController($event);
    }

    /**
     * @test
     */
    public function doesNothingIfControllerIsNotSupported()
    {
        $reader = $this->getConfigurationReader();

        $reader
            ->expects($this->never())
            ->method('read');

        $listener = new FlowConfigurationListener($reader);

        $event = $this->getEventMock(true);

        $listener->onKernelController($event);
    }

    /**
     * @test
     */
    public function updatesRequestAttributesWithReadConfiguration()
    {
        $flowStepsAttrName = 'devhelp.flow_control.next_steps';
        $autocommitAttrName = 'devhelp.flow_control.autocommit';

        $expectedFlowStepsValue = 'flowStepsTestValue';
        $expectedAutocommitValue = 'autocommitTestValue';

        $configuration = array(
            'flow_steps' => $expectedFlowStepsValue,
            'autocommit' => $expectedAutocommitValue,
        );

        $reader = $this->getConfigurationReader(true, $configuration);

        $listener = new FlowConfigurationListener($reader);

        $request = new Request();

        $this->assertNull($request->attributes->get($flowStepsAttrName));
        $this->assertNull($request->attributes->get($autocommitAttrName));

        $event = $this->getEventMock(true, $request);

        $listener->onKernelController($event);

        $this->assertEquals($expectedFlowStepsValue, $request->attributes->get($flowStepsAttrName));
        $this->assertEquals($expectedAutocommitValue, $request->attributes->get($autocommitAttrName));
    }

    /**
     * @test
     */
    public function subscribesOnlyOnKernelControllerEvent()
    {
        $listener = new FlowConfigurationListener($this->getConfigurationReader());

        $expected = array(
            KernelEvents::CONTROLLER => array ('onKernelController', 1000)
        );

        $this->assertSame($expected, $listener->getSubscribedEvents());
    }

    private function getConfigurationReader($supports = false, $configuration = null)
    {
        $readerClass = 'Devhelp\FlowControlBundle\FlowConfiguration\Reader\ConfigurationReaderInterface';

        $mock = $this->getMockBuilder($readerClass)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue($supports));

        $mock
            ->expects($this->any())
            ->method('read')
            ->will($this->returnValue($configuration));

        return $mock;
    }

    private function getEventMock($isMasterRequest = false, $request = null)
    {
        $mock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterControllerEvent')
                     ->disableOriginalConstructor()
                     ->getMock();

        $mock
            ->expects($this->any())
            ->method('isMasterRequest')
            ->will($this->returnValue($isMasterRequest));

        $mock
            ->expects($this->exactly($isMasterRequest ? 1 : 0))
            ->method('getController');

        $mock
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        return $mock;
    }
}
