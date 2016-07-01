<?php

namespace P4l\Pikabot\Listener;

trait OptionsAwareTrait
{
    private $options;
    
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }
}