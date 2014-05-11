<?php

namespace Devhelp\FlowControlBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

class DevhelpFlowControlExtension extends Extension
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Loader\FileLoader;
     */
    protected $loader;

    /**
     * @var ContainerBuilder
     */
    protected $container;

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();

        $this->config = $this->processConfiguration($configuration, $configs);
        $this->loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $this->container = $container;

        $this->loadCoreServices();

        $this->loadReader();

        $this->loadActionListeners();

        $this->registerFlows();
    }

    private function loadCoreServices()
    {
        $this->loader->load('services.yml');
    }

    private function loadReader()
    {
        $driver = $this->config['reader']['driver'];
        $file = 'reader.'.$driver.'.yml';

        $this->loader->load($file);
    }

    private function loadActionListeners()
    {
        if ($this->config['listeners']['action']) {
            $this->loader->load('action_listeners.yml');
        }
    }

    private function registerFlows()
    {
        $this->container->setParameter('devhelp.flow_control.flow_definitions', $this->config['flows']);
    }
}
