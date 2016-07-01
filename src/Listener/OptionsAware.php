<?php

namespace P4l\Pikabot\Listener;

interface OptionsAware
{
    public function setOptions(array $options);
    public function getOptions();
}