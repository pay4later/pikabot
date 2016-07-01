<?php

namespace P4l\Pikabot\Http;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Interop\Container\ContainerInterface;
use WyriHaximus\React\GuzzlePsr7\HttpClientAdapter;

class GuzzleClientFactory
{
    public static function createService(ContainerInterface $c)
    {
        return new Client([
            'handler' => HandlerStack::create($c->get(HttpClientAdapter::class)),
            'headers' => [ 'User-Agent' => 'Pay4Later Pikabot 160701' ]
        ]);
    }
}