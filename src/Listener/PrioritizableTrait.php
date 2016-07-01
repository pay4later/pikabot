<?php

namespace P4l\Pikabot\Listener;

trait PrioritizableTrait
{
    private $priority;
    
    public function setPriority($priority)
    {
        $this->priority = (int)$priority;

        return $this;
    }

    public function getPriority()
    {
        return $this->priority;
    }
}