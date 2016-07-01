<?php

namespace P4l\Pikabot\Listener;

interface ChannelAware
{
    public function setChannels(array $channels);
    public function getChannels();
}