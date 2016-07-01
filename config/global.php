<?php

return [
    'config' => [
        'api_token' => '...',
    ],

    'factories' => [
        React\EventLoop\LoopInterface::class => DI\factory([React\EventLoop\Factory::class, 'create']),
        Slack\RealTimeClient::class => DI\factory([P4l\Pikabot\Http\SlackClientFactory::class, 'createService']),
        P4l\Pikabot\Message\Client::class => DI\factory([P4l\Pikabot\Message\ClientFactory::class, 'createService']),
        GuzzleHttp\ClientInterface::class => DI\factory([P4l\Pikabot\Http\GuzzleClientFactory::class, 'createService'])
    ],

    'aliases' => [
        'EventLoop' => DI\get(React\EventLoop\LoopInterface::class),
        'SlackClient' => DI\get(Slack\ApiClient::class),
        'Pikabot' => DI\get(P4l\Pikabot\Message\ClientFactory::class),
        'GuzzleHttp' => DI\get(GuzzleHttp\ClientInterface::class)
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