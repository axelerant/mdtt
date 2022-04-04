<?php

declare(strict_types=1);

namespace Mdtt\Destination;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\StreamWrapper;
use JsonMachine\Exception\InvalidArgumentException;
use JsonMachine\Items;
use Mdtt\DataSource;
use Mdtt\Exception\ExecutionException;
use Mdtt\Utility\HttpClient;
use Psr\Http\Client\ClientExceptionInterface;

class Json extends DataSource
{
    private ?string $authBasicCredential;
    private HttpClient $httpClient;
    private string $selector;
    private int $offset;

    public function __construct(
        string $data,
        string $selector,
        HttpClient $httpClient,
        int $offset = 0,
        string $authBasicCredential = null
    ) {
        parent::__construct($data);
        $this->selector = $selector;
        $this->httpClient = $httpClient;

        $this->offset = $offset;
        $this->authBasicCredential = $authBasicCredential;
    }

    /**
     * @return string
     */
    public function getSelector(): string
    {
        return $this->selector;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @inheritDoc
     */
    public function getItem(): ?array
    {
        $httpClient = $this->httpClient->getClient();
        $request = new Request('GET', $this->data);

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
            $items = Items::fromStream($responseStream);
            $itemsIterator = $items->getIterator();
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
        // end - setup.
        // start - getItem.

        foreach ($itemsIterator as $item) {
            return $item;
        }

        return null;
    }
}
