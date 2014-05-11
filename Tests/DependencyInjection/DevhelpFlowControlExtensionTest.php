<?php

namespace Devhelp\FlowControlBundle\Tests\DependencyInjection;


use Devhelp\FlowControlBundle\DependencyInjection\DevhelpFlowControlExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DevhelpFlowControlExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DevhelpFlowControlExtension
     */
    private $extension;

    /**
     * @var ContainerBuilder
     */
    private $container;

    private $minConfiguration = array(
        'reader' => array(
            'driver' => 'annotation'
        ),
        'flows' => array(
            'fake_flow' => array(
                'moves' => array(
                    'step_a' => array('step_b'),
                    'step_b' => array('step_a', 'step_c'),
                    'step_c' => array('step_b'),
                ),
                'entry_points' => array('step_a')
            ),
        )
    );

    public function testCoreServicesAreLoaded()
    {
        $this->extension->load(array($this->minConfiguration), $this->container);

        $this->assertTrue($this->container->hasDefinition('devhelp.flow_control.flow_step_builder'));
        $this->assertTrue($this->container->hasDefinition('devhelp.flow_control.flow_builder'));
        $this->assertTrue($this->container->hasDefinition('devhelp.flow_control.flow_repository.lazy'));
        $this->assertTrue($this->container->hasDefinition('devhelp.flow_control'));
        $this->assertTrue($this->container->hasDefinition('devhelp.flow_control.current_steps.session'));
        $this->assertTrue($this->container->hasAlias('devhelp.flow_control.flow_repository'));
        $this->assertTrue($this->container->hasAlias('devhelp.flow_control.current_steps'));
    }

    /**
     * @dataProvider providerReader
     */
    public function testReaderServiceIsLoadedAccordingToConfiguration($reader)
    {
        $config = $this->minConfiguration;

        $config['reader'] = array('driver' => $reader);

        $this->extension->load(array($config), $this->container);

        $this->assertEquals(
            $this->container->getDefinition('devhelp.flow_control.configuration_reader.' . $reader),
            $this->container->findDefinition('devhelp.flow_control.configuration_reader')
        );
    }

    public function providerReader()
    {
        return array(
            array('annotation')
        );
    }

    public function testActionListenersAreLoadedIfFlagEqualsTrue()
    {
        $this->extension->load(array($this->minConfiguration), $this->container);

        $this->assertTrue($this->container->hasDefinition('devhelp.flow_control.listener.flow_configuration'));
        $this->assertTrue($this->container->hasDefinition('devhelp.flow_control.listener.flow_control'));
    }

    public function testActionListenersAreNotLoadedIfFlagEqualsFalse()
    {
        $config = $this->minConfiguration;
        $config['listeners']['action'] = false;

        $this->extension->load(array($config), $this->container);

        $this->assertFalse($this->container->hasDefinition('devhelp.flow_control.listener.flow_configuration'));
        $this->assertFalse($this->container->hasDefinition('devhelp.flow_control.listener.flow_control'));
    }

    protected function setUp()
    {
        $this->extension = new DevhelpFlowControlExtension();

        $container = new ContainerBuilder();
        $container->setParameter('kernel.cache_dir', sys_get_temp_dir());

        $this->container = $container;
    }
}
