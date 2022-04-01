<?php

declare(strict_types=1);

namespace Mdtt\Utility;

use Symfony\Component\HttpClient\HttpClient as MainHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClient
{
    private HttpClientInterface $client;

    public function getClient(): HttpClientInterface
    {
        if (!isset($this->client)) {
            $this->client = MainHttpClient::create();
        }

        return $this->client;
    }
}
