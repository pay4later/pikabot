<?php

use Interop\Container\ContainerInterface;

return [
    'config' => [
        'api_token' => '...',
    ],

    'factories' => [
        'eventLoop' => DI\factory([React\EventLoop\Factory::class, 'create']),

        GuzzleHttp\ClientInterface::class => DI\object(GuzzleHttp\Client::class)
            ->constructor([
                'headers' => [
                    'User-Agent' => 'Pay4Later Pikabot 160701'
                ]]),

        'guzzleHttp' => DI\get(GuzzleHttp\ClientInterface::class),

        'slackClient' => function (ContainerInterface $c) {
            $client = new Slack\RealTimeClient($c->get('eventLoop'));
            $client->setToken($c->get('config')['api_token']);

            return $client;
        },

        P4l\Pikabot\Client::class => DI\factory([P4l\Pikabot\ClientFactory::class, 'create'])
    ],

    'listeners' => [
        P4l\Pikabot\Listener\Adapter\EchoAdapter::class => [
            'priority' => 100,
            'channels' => '*'
        ],

        P4l\Pikabot\Listener\Adapter\JarvisAdapter::class => [
            'priority' => 20,
            'channels' => '*',
            'options' => [
                'endpoint' => '...',
                'job' => '...',
                'username' => '...',
                'password' => '...'
            ]
        ]
    ]
];