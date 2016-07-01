<?php

namespace P4l\Pikabot;

use P4l\Pikabot\Listener\Listener;
use Slack\ApiClient;
use Slack\Payload;
use Slack\User;

class Client
{
    /**
     * @var ApiClient
     */
    private $client;

    /**
     * @var Listener[]
     */
    private $listeners;

    /**
     * @var User
     */
    private $user;

    public function __construct(ApiClient $client, array $listeners)
    {
        $this->client = $client;
        $this->listeners = $listeners;
    }

    public function onConnect()
    {
        $this->client->getAuthedUser()->then(function (User $user) {
            $this->user = $user;
            $this->client->on('message', [$this, 'onMessage']);
        });
    }

    public function onMessage(Payload $payload)
    {
        if ($payload['type'] !== 'message' || $payload['user'] === $this->user->getId()) {
            return;
        }

        $message = new Message($this->client, $this->listeners, $payload);
        $this->client->getUserById($payload['user'])->then([$message, 'onGetUserById']);
    }
}