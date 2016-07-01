<?php

namespace P4l\Pikabot\Listener\Adapter;

use P4l\Pikabot\Listener\AbstractListener;
use Slack\ApiClient;
use Slack\Channel;
use Slack\Payload;
use Slack\User;

class EchoAdapter extends AbstractListener
{
    public function process(ApiClient $client, Payload $payload, User $user, Channel $channel)
    {
        $message = sprintf('%s said %s in channel %s', $user->getUsername(), $payload['text'], $channel->getName());
        $client->send($message, $channel);

        return true;
    }
}