<?php

namespace P4l\Pikabot\Listener\Adapter;

use P4l\Pikabot\Listener\AbstractListener;
use Slack\ApiClient;
use Slack\Channel;
use Slack\Payload;
use Slack\User;

class FileAdapter extends AbstractListener
{
    private $lastFileRead = 0;
    private $lines = [];

    public function process(ApiClient $client, Payload $payload, User $user, Channel $channel)
    {
        $probability = 100 * $this->getOptions()['probability'];
        $rand = mt_rand(1, 100);

        if ($rand <= $probability) {
            $lines = $this->getLines();
            $message = $lines[mt_rand(0, count($lines) - 1)];
            $client->send($message, $channel);
        }

        return true;

    }

    public function getOptions()
    {
        return parent::getOptions() + [ // merge default values
            'path'        => null,
            'probability' => 1,
            'separator'   => "\n",
            'ttl'         => 5
        ];
    }

    private function getLines()
    {
        $options = $this->getOptions();
        $ttl = (int)$options['ttl'];

        // refresh file contents if:
        // * lines is empty
        // * ttl is less than 0
        // * ttl is less than or equal time elapsed since last read
        if (!$this->lines || $ttl < 0 || $ttl <= microtime(true) - $this->lastFileRead) {
            if (!file_exists($options['path']) || !is_readable($options['path'])) {
                throw new \RuntimeException("File not readable: {$options['path']}");
            }

            $this->lastFileRead = microtime(true);
            $contents = file_get_contents($options['path']);
            $this->lines = array_filter(explode($options['separator'], $contents));
        }

        return $this->lines;
    }
}