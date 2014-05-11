<?php

namespace Devhelp\FlowControlBundle\Tests\CurrentSteps;


use Devhelp\FlowControlBundle\CurrentSteps\CurrentSessionSteps;

class CurrentSessionStepsTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function allReturnsEmptyArrayIfNoStepsAreStoredInSession()
    {
        $session = $this->getSessionMock();

        $currentSteps = new CurrentSessionSteps($session);

        $this->assertSame(array(), $currentSteps->all());
    }

    /**
     * @test
     */
    public function allReturnsStepsArrayFromJSONStoredInSession()
    {
        $json = '{"example_flow_a" : "example_step_a", "example_flow_b" : "example_step_a"}';

        $session = $this->getSessionMock(true, $json);

        $currentSteps = new CurrentSessionSteps($session);

        $expected = array(
            'example_flow_a' => 'example_step_a',
            'example_flow_b' => 'example_step_a'
        );

        $this->assertSame($expected, $currentSteps->all());
    }

    /**
     * @test
     * @dataProvider providerFlows
     */
    public function getReturnsStepsForGivenFlows($flows, $expected)
    {
        $json = '{"example_flow_a" : "example_step_a", "example_flow_b" : "example_step_a"}';

        $session = $this->getSessionMock(true, $json);

        $currentSteps = new CurrentSessionSteps($session);

        $this->assertSame($expected, $currentSteps->get($flows));
    }

    public function providerFlows()
    {
        return array(
            array(
                array('example_flow_a'),
                array('example_flow_a' => 'example_step_a')
            ),
            array(
                array('example_flow_b'),
                array('example_flow_b' => 'example_step_a')
            ),
            array(
                array('example_flow_a', 'example_flow_b'),
                array(
                    'example_flow_a' => 'example_step_a',
                    'example_flow_b' => 'example_step_a'
                )
            ),
        );
    }

    /**
     * @test
     * @dataProvider providerUpdate
     */
    public function update($newSteps, $expected)
    {
        $json = '{"example_flow_a" : "example_step_a", "example_flow_b" : "example_step_a"}';

        $session = $this->getSessionMock(true, $json, true);

        $session
            ->expects($this->exactly(1))
            ->method('set')
            ->with(CurrentSessionSteps::CURRENT_STEPS, $expected);

        $currentSteps = new CurrentSessionSteps($session);

        $currentSteps->update($newSteps);
    }

    public function providerUpdate()
    {
        return array(
            array(
                array('example_flow_a' => 'example_step_b'),
                '{"example_flow_a":"example_step_b","example_flow_b":"example_step_a"}'
            ),
            array(
                array('example_flow_b' => 'example_step_a'),
                '{"example_flow_a":"example_step_a","example_flow_b":"example_step_a"}'
            ),
            array(
                array('example_flow_c' => 'example_step_a'),
                '{"example_flow_a":"example_step_a","example_flow_b":"example_step_a",'.
                '"example_flow_c":"example_step_a"}'
            ),
        );
    }

    private function getSessionMock($hasValue = false, $getValue = array(), $isSetCalled = false)
    {
        $mock = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\SessionInterface')
                     ->disableOriginalConstructor()
                     ->getMock();

        $mock
            ->expects($this->any())
            ->method('has')
            ->will($this->returnValue($hasValue));

        $mock
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($getValue));

        $mock
            ->expects($this->exactly((int) $isSetCalled))
            ->method('set');

        return $mock;
    }
}
