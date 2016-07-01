<?php

namespace P4l\Pikabot\Listener\Adapter;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use P4l\Pikabot\Listener\AbstractListener;
use Slack\ApiClient;
use Slack\Channel;
use Slack\Payload;
use Slack\User;

class JarvisAdapter extends AbstractListener
{
    private $guzzle;

    public function __construct(ClientInterface $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function process(ApiClient $client, Payload $payload, User $user, Channel $channel)
    {
        if (!preg_match('~^jarvis uat (?<branch>[A-Za-z0-9_/-]+)~', $payload['text'], $match)) {
            return true;
        }

        try {
            $options = $this->getOptions();
            $url = $this->getUrl($options, $match['branch'], $channel->getName());
            $request = $this->guzzle->requestAsync('POST', $url, ['auth' => [$options['username'], $options['password']]]);
            $request->then(
                function (Response $response) use ($user, $match, $client, $channel) {
                    if (($status = $response->getStatusCode()) === 201) {
                        $message = "@{$user->getUsername()} your environment for {$match['branch']} is being prepared";
                    } else {
                        $message = trim("Environment creation failed {$response->getBody()->getContents()}");
                    }
                    $client->send($message, $channel);
                },
                function () use ($client, $channel) {
                    $client->send('Environment creation error', $channel);
                }
            );


        } catch (GuzzleException $e) {
            trigger_error($e->getMessage());
            $client->send('Environment creation error', $channel);
        }

        return false;
    }
    
    private function getUrl(array $options, $branch, $channel)
    {
        $query = [
            'Subdomain'                 => uniqid(),
            'p4lmainVersion'            => $branch,
            'sisVersion'                => $branch,
            'backofficev3Version'       => $branch,
            'backofficeVersion'         => $branch,
            'slackNotifierTarget'       => "#{$channel}",
            'hoursToStay'               => 1
        ];

        return "{$options['endpoint']}/view/All/job/{$options['job']}/buildWithParameters?" . http_build_query($query);
    }
}