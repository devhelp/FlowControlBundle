<?php

namespace Devhelp\FlowControlBundle\CurrentSteps;


interface CurrentStepsInterface
{
    public function get(array $flows);
    public function update(array $newSteps = array());
}
