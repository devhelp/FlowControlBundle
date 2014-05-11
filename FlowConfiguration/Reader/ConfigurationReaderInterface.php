<?php

namespace Devhelp\FlowControlBundle\FlowConfiguration\Reader;

interface ConfigurationReaderInterface
{
    /**
     * reads configuration for controller
     *
     * @param $controller
     * @return array
     */
    public function read($controller);

    /**
     * checks if reader supports reading the configuration from given controller
     *
     * @param $controller
     * @return boolean
     */
    public function supports($controller);
}
