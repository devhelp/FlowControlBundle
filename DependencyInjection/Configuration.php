<?php

namespace Devhelp\FlowControlBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('devhelp_flow_control');

        $this->addListenersNode($rootNode);

        $this->addReaderNode($rootNode);

        $this->addFlowsNode($rootNode);

        return $treeBuilder;
    }

    protected function addListenersNode(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('listeners')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('action')
                            ->defaultTrue()
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    protected function addReaderNode(ArrayNodeDefinition $node)
    {
        $supportedDrivers = array(
            'annotation',
        );

        $supportedDriversString = 'Supported drivers: '.implode(', ', $supportedDrivers);

        $node
            ->children()
                ->arrayNode('reader')
                    ->children()
                        ->scalarNode('driver')
                            ->info($supportedDriversString)
                            ->cannotBeEmpty()
                            ->isRequired()
                            ->validate()
                                ->ifNotInArray($supportedDrivers)
                                ->thenInvalid('The driver %s is not supported. '.$supportedDriversString)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    protected function addFlowsNode(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('flows')
                    ->example(array(
                        'my_flow' => array(
                            'moves' => array(
                                'step_1' => array('step_2'),
                                'step_2' => array('step_1', 'step_3', 'step_4'),
                                'step_3' => array('step_2', 'step_4'),
                                'step_4' => array('step_2', 'step_3'),
                            ),
                            'entry_points' => array('step_1', 'step_2')
                        )
                    ))
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('moves')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->prototype('array')
                                    ->prototype('scalar')
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('entry_points')
                                ->info('array of steps that are entry points for the flow')
                                ->prototype('scalar')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
