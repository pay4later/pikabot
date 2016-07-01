<?php

namespace P4l\Pikabot\Message;

use P4l\Pikabot\Listener\ChannelAware;
use P4l\Pikabot\Listener\Listener;
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
     * @var Listener[]
     */
    private $listeners;
    
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

    public function __construct(ApiClient $client, array $listeners, Payload $payload)
    {
        $this->client     = $client;
        $this->listeners  = $listeners;
        $this->payload    = $payload;
    }

    public function onGetUserById(User $user)
    {
        $this->user = $user;
        return $this->client->getChannelById($this->payload['channel'])->then([$this, 'onGetChannelById']);
    }

    public function onGetChannelById(Channel $channel)
    {
        $this->channel = $channel;
        
        foreach ($this->listeners as $listener) {
            if ($listener instanceof ChannelAware) {
                $channels = $listener->getChannels();
                array_walk($channels, function (&$v) {
                    $v = strtolower(str_replace('#', '', $v));
                });
                if (!in_array('*', $channels, true) && !in_array(strtolower($channel->getName()), $channels, true)) {
                    continue;
                }
            }

            if (!$listener->process($this->client, $this->payload, $this->user, $this->channel)) {
                break;
            }
        }
    }
}