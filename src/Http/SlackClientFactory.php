<?php

namespace P4l\Pikabot\Http;

use Interop\Container\ContainerInterface;
use React\EventLoop\LoopInterface;
use Slack\RealTimeClient;

class SlackClientFactory
{
    public static function createService(ContainerInterface $c)
    {
        $client = new RealTimeClient($c->get(LoopInterface::class));
        $client->setToken($c->get('config')['api_token']);

        return $client;
    }
}