<?php

namespace Devhelp\FlowControlBundle\CurrentSteps;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CurrentSessionSteps implements CurrentStepsInterface
{
    const CURRENT_STEPS = 'devhelp.flow_control.current_steps';

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function get(array $flows)
    {
        $flowsSteps = array_intersect_key($this->all(), array_flip($flows));

        return $flowsSteps;
    }

    public function update(array $newSteps = array())
    {
        $oldSteps = $this->all();

        $currentSteps = array_merge($oldSteps, $newSteps);

        $this->session->set(self::CURRENT_STEPS, json_encode($currentSteps));
    }

    public function all()
    {
        if (!$this->session->has(self::CURRENT_STEPS)) {
            return array();
        }

        return json_decode($this->session->get(self::CURRENT_STEPS), true);
    }
}
