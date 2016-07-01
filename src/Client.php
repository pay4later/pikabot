<?php

namespace P4l\Pikabot;

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
     * @var User
     */
    private $user;

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
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

        $message = new Message($this->client, $payload);
        $this->client->getUserById($payload['user'])->then([$message, 'onGetUserById']);
    }
}