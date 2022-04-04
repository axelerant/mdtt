<?php

declare(strict_types=1);

namespace Mdtt\Utility\DataSource;

use GuzzleHttp\Psr7\Request;
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
    private ?string $authBasicCredential;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $authBasicCredential
     */
    public function setAuthBasicCredential(string $authBasicCredential): void
    {
        $this->authBasicCredential = $authBasicCredential;
    }

    public function getItems(string $data, string $selector): \Iterator
    {
        $httpClient = $this->httpClient->getClient();
        $request = new Request('GET', $data);

        if ($this->authBasicCredential !== null) {
            $credential = explode(':', $this->authBasicCredential);
            $request->withHeader('auth', $credential);
        }

        try {
            $response = $httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new ExecutionException($e->getMessage());
        }

        if ($response->getStatusCode() !== 200) {
            throw new ExecutionException("Unable to receive 200 OK response from the endpoint.");
        }

        $responseStream = StreamWrapper::getResource($response->getBody());
        try {
            /** @var \Iterator $items */
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
                    "Something went wrong while looping through the response items. MessageL %s",
                    $e->getMessage()
                )
            );
        }

        return $items;
    }
}
