<?php

namespace P4l\Pikabot\Listener;

use Slack\ApiClient;
use Slack\Channel;
use Slack\Payload;
use Slack\User;

interface Listener
{
    /**
     * @param ApiClient $client
     * @param Payload $payload
     * @param User $user
     * @param Channel $channel
     *
     * @return bool
     */
    public function process(ApiClient $client, Payload $payload, User $user, Channel $channel);
}