<?php

namespace Devhelp\FlowControlBundle\FlowConfiguration\Reader;

use Devhelp\FlowControlBundle\FlowConfiguration\Annotation\DisableAutocommit;
use Devhelp\FlowControlBundle\FlowConfiguration\Annotation\Flow;
use Doctrine\Common\Annotations\Reader;

class AnnotationConfigurationReader implements ConfigurationReaderInterface
{

    private $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @param $controller
     * @return array
     */
    public function read($controller)
    {
        $controllerAction = new \ReflectionMethod($controller[0], $controller[1]);

        $flowSteps = array();
        $autocommit = true;

        $annotations = $this->annotationReader->getMethodAnnotations(
            $controllerAction
        );

        foreach ($annotations as $annotation) {

            if ($annotation instanceof Flow) {
                $flowSteps[$annotation->getName()] = $annotation->getStep();
            }

            if ($annotation instanceof DisableAutocommit) {
                $autocommit = false;
            }
        }

        return array(
            'flow_steps' => $flowSteps,
            'autocommit' => $autocommit
        );
    }

    /**
     * @param mixed $controller
     *
     * @return boolean
     */
    public function supports($controller)
    {
        return is_array($controller) && isset($controller[0]) && isset($controller[1]);
    }
}
