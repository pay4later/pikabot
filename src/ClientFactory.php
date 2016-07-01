<?php

namespace P4l\Pikabot;

use Interop\Container\ContainerInterface;
use P4l\Pikabot\Listener\ChannelAware;
use P4l\Pikabot\Listener\OptionsAware;
use P4l\Pikabot\Listener\Prioritizable;

class ClientFactory
{
    public static function create(ContainerInterface $container)
    {
        $listeners = [];

        foreach ($container->get('listeners') as $listener => $options) {
            $listener = $container->get($listener);
            $options += ['priority' => 0, 'channels' => '', 'options' => []];

            if ($listener instanceof ChannelAware) {
                $channels = is_array($options['channels'])
                    ? $options['channels']
                    : preg_split('/[,;: ]+/', $options['channels'], -1, PREG_SPLIT_NO_EMPTY);
                $listener->setChannels($channels);
            }

            if ($listener instanceof Prioritizable) {
                $listener->setPriority($options['priority']);
            }

            if ($listener instanceof OptionsAware) {
                $listener->setOptions($options['options']);
            }

            $listeners[] = $listener;
        }

        usort($listeners, function ($a, $b) {
            $priorityA = $a instanceof Prioritizable ? $a->getPriority() : 0;
            $priorityB = $b instanceof Prioritizable ? $b->getPriority() : 0;

            return $priorityA - $priorityB;
        });

        return new Client($container->get('slackClient'), $listeners);
    }
}