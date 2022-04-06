<?php

declare(strict_types=1);

namespace Mdtt\Destination;

use Iterator;
use JsonMachine\Items;
use Mdtt\DataSource;
use Mdtt\Utility\DataSource\Json as JsonDataSourceUtility;

class Json extends DataSource
{
    private string $selector;
    private JsonDataSourceUtility $jsonDataSourceUtility;
    private Items|null $items;

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
     * @inheritDoc
     */
    public function getItem(): Iterator
    {
        if (!isset($this->items)) {
            $this->items = $this->jsonDataSourceUtility->getItems($this->data, $this->selector);
        }

        foreach ($this->items as $item) {
            yield $item;
        }
    }
}
