<?php

use Interop\Container\ContainerInterface;

return [
    'config' => [
        'api_token' => '...',
    ],

    'factories' => [
        'eventLoop' => DI\factory([React\EventLoop\Factory::class, 'create']),

        WyriHaximus\React\GuzzlePsr7\HttpClientAdapter::class => DI\object(WyriHaximus\React\GuzzlePsr7\HttpClientAdapter::class)
            ->constructor(DI\get('eventLoop')),

        GuzzleHttp\ClientInterface::class => function (ContainerInterface $c) {
            $handler = $c->get(WyriHaximus\React\GuzzlePsr7\HttpClientAdapter::class);
            $client = new GuzzleHttp\Client([
                'headers' => [ 'User-Agent' => 'Pay4Later Pikabot 160701' ],
                'handler' => GuzzleHttp\HandlerStack::create($handler)
            ]);

            return $client;
        },

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