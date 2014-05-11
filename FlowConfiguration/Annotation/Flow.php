<?php

namespace Devhelp\FlowControlBundle\FlowConfiguration\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *   @Attribute("name", type = "string"),
 *   @Attribute("step", type = "string"),
 * })
 */
class Flow
{
    private $name;
    private $step;

    public function __construct(array $values)
    {
        $this->name = $values['name'];
        $this->step = $values['step'];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getStep()
    {
        return $this->step;
    }
}
