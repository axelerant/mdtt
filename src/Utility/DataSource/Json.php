<?php

declare(strict_types=1);

namespace Mdtt\Utility\DataSource;

use GuzzleHttp\Psr7\StreamWrapper;
use JsonMachine\Exception\InvalidArgumentException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use Mdtt\Exception\ExecutionException;
use Mdtt\Utility\HttpClient;
use Psr\Http\Client\ClientExceptionInterface;

class Json
{
    private HttpClient $httpClient;

    private ?string $username;
    private ?string $password;
    private ?string $protocol;

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @param string $protocol
     */
    public function setProtocol(string $protocol): void
    {
        $this->protocol = $protocol;
    }

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getItems(string $data, string $selector): Items
    {
        $httpClient = $this->httpClient->getClient();
        $headers = [];

        if (isset($this->username, $this->password, $this->protocol)) {
            $headers['auth'] = [$this->username, $this->password, $this->protocol];
        }

        try {
            $response = $httpClient->request('GET', $data, $headers);
        } catch (ClientExceptionInterface $e) {
            throw new ExecutionException($e->getMessage());
        }

        if ($response->getStatusCode() !== 200) {
            throw new ExecutionException("Unable to receive 200 OK response from the endpoint.");
        }

        $responseStream = StreamWrapper::getResource($response->getBody());
        try {
            $items = Items::fromStream($responseStream, [
              'pointer' => $selector,
              'decoder' => new ExtJsonDecoder(true),
            ]);
        } catch (InvalidArgumentException $e) {
            throw new ExecutionException(
                sprintf(
                    "Something went wrong while streaming items from the response. Message: %s",
                    $e->getMessage()
                )
            );
        } catch (\Exception $e) {
            throw new ExecutionException(
                sprintf(
                    "Something went wrong while looping through the response items. Message: %s",
                    $e->getMessage()
                )
            );
        }

        return $items;
    }
}
