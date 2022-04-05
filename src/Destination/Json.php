<?php

declare(strict_types=1);

namespace Mdtt\Destination;

use JsonMachine\Items;
use Mdtt\DataSource;
use Mdtt\Utility\DataSource\Json as JsonDataSourceUtility;

class Json extends DataSource
{
    private string $selector;
    private JsonDataSourceUtility $jsonDataSourceUtility;
    private Items|null $items;
    private \Generator|null $itemsIterator;

    public function __construct(
        string $data,
        string $selector,
        JsonDataSourceUtility $jsonDataSourceUtility
    ) {
        parent::__construct($data);
        $this->selector = $selector;
        $this->jsonDataSourceUtility = $jsonDataSourceUtility;
    }

    /**
     * @return string
     */
    public function getSelector(): string
    {
        return $this->selector;
    }

    /**
     * @throws \Exception
     */
    private function getItemsIterator(): \Generator
    {
        return $this->items->getIterator();
    }

    /**
     * @inheritDoc
     */
    public function getItem(): ?array
    {
        if (!isset($this->itemsIterator)) {
            $this->items = $this->jsonDataSourceUtility->getItems($this->data, $this->selector);
            $this->itemsIterator = $this->getItemsIterator();
        }

        return $this->itemsIterator->current();
    }
}
