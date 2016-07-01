<?php

namespace P4l\Pikabot\Listener;

trait ChannelAwareTrait
{
    private $channels;
    
    public function setChannels(array $channels)
    {
        $this->channels = $channels;

        return $this;
    }

    public function getChannels()
    {
        return $this->channels;
    }
}