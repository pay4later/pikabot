<?php

return [
    'api_token' => '...',
    'listeners' => [
        \P4l\Pikabot\Listener\Adapter\EchoAdapter::class => [
            'priority' => 100,
            'channels' => [ '*' ]
        ]
    ]
];