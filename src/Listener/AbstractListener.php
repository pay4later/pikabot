<?php

namespace P4l\Pikabot\Listener;

abstract class AbstractListener implements Listener, ChannelAware, Prioritizable
{
    use ChannelAwareTrait;
    use PrioritizableTrait;
}