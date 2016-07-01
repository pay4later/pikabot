<?php

namespace P4l\Pikabot;

use Slack\ApiClient;
use Slack\Channel;
use Slack\Payload;
use Slack\User;

class Message
{
    /**
     * @var ApiClient
     */
    private $client;
    
    /**
     * @var Payload
     */
    private $payload;
    
    /**
     * @var User
     */
    private $user;
    
    /**
     * @var Channel
     */
    private $channel;

    public function __construct(ApiClient $client, Payload $payload)
    {
        $this->client  = $client;
        $this->payload = $payload;
    }

    public function onGetUserById(User $user)
    {
        $this->user = $user;
        return $this->client->getChannelById($this->payload['channel'])->then([$this, 'onGetChannelById']);
    }

    public function onGetChannelById(Channel $channel)
    {
        $this->channel = $channel;
        $this->process();
    }

    protected function process()
    {
        $this->client->send("{$this->user->getUsername()} typed a message: {$this->payload['text']}", $this->channel);
    }
}