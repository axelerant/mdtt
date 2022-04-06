<?php

declare(strict_types=1);

namespace Mdtt\Utility;

use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;

class HttpClient
{
    private ClientInterface $client;

    public function getClient(): ClientInterface
    {
        if (!isset($this->client)) {
            $this->client = new Client();
        }

        return $this->client;
    }
}
