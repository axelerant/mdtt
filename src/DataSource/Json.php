<?php

declare(strict_types=1);

namespace Mdtt\DataSource;

use Iterator;
use JsonMachine\Items;
use Mdtt\Utility\DataSource\Json as JsonDataSourceUtility;

class Json extends DataSource
{
    private string $selector;

    private ?string $key;

    private JsonDataSourceUtility $jsonDataSourceUtility;

    private Items|null $items;

    private ?string $username;

    private ?string $password;

    private ?string $protocol;

    public function __construct(
        string $data,
        string $selector,
        JsonDataSourceUtility $jsonDataSourceUtility,
        string $key
    ) {
        parent::__construct($data);
        $this->selector = $selector;
        $this->jsonDataSourceUtility = $jsonDataSourceUtility;
        $this->key = $key;
    }

    /**
     * @param  string|null  $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @param  string|null  $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @param  string|null  $protocol
     */
    public function setProtocol(?string $protocol): void
    {
        $this->protocol = $protocol;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Iterator
    {
        if (!isset($this->items)) {
            $this->items = $this->jsonDataSourceUtility->getItems(
                $this->data,
                $this->selector,
                $this->username,
                $this->password,
                $this->protocol
            );
        }

        foreach ($this->items as $key => $item) {
            if (isset($this->key) && $key === $this->key) {
                yield $item;
            }
        }
    }
}
