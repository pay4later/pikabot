<?php

namespace P4l\Pikabot\Listener;

abstract class AbstractListener implements Listener, ChannelAware, OptionsAware, Prioritizable
{
    use ChannelAwareTrait;
    use OptionsAwareTrait;
    use PrioritizableTrait;
}