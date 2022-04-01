<?php

declare(strict_types=1);

namespace Mdtt\Source;

use Mdtt\DataSource;
use Mdtt\Exception\ExecutionException;
use Mdtt\Utility\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

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
        try {
            if ($this->authBasicCredential !== null) {
                $response = $httpClient->request('GET', $this->data, [
                  'auth_basic' => $this->authBasicCredential,
                ]);
            } else {
                $response = $httpClient->request('GET', $this->data);
            }

            $responseStatusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new ExecutionException($e->getMessage());
        }

        if ($responseStatusCode !== 200) {
            throw new ExecutionException("Unable to receieve 200 OK response from the endpoint.");
        }

        foreach ($httpClient->stream($response) as $item) {
            var_dump($item);
        }

        return ['hello' => 'hello'];
    }
}
