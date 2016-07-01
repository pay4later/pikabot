<?php

namespace P4l\Pikabot\Listener;

interface Prioritizable
{
    public function setPriority($priority);
    public function getPriority();
}