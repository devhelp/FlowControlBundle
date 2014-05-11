<?php

namespace Devhelp\FlowControlBundle\Exception;

class NoValidStepsFoundException extends \Exception
{

    public function __construct(array $steps, $code = 0, \Exception $previous = null)
    {
        $moves = array();

        foreach ($steps as $flowId => $step) {
            $moves[] = "step: $step, in flow: $flowId";
        }

        $movesString = implode(PHP_EOL, $moves);

        $message = sprintf("None of the steps is valid:".PHP_EOL."'%s'", $movesString);

        parent::__construct($message, $code, $previous);
    }
}
