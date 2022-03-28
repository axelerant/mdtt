<?php

declare(strict_types=1);

namespace Mdtt\Destination;

use Mdtt\DataSource;
use Mdtt\Exception\ExecutionException;
use Mdtt\Exception\SetupException;
use Mdtt\Utility\DataSource\Database;

class Query extends DataSource
{
    private \mysqli_result $resultSet;

    /**
     * @inheritDoc
     */
    public function getItem(): ?array
    {
        $specification = require "tests/mdtt/spec.php";

        /** @var array<array<string>> $databases */
        $databases = $specification['databases'];
        if (!isset(
            $databases['destination_db']['database'],
            $databases['destination_db']['username'],
            $databases['destination_db']['password'],
            $databases['destination_db']['host'],
            $databases['destination_db']['port']
        )) {
            throw new SetupException("Destination database not specified correctly");
        }

        if (!isset($this->resultSet)) {
            $this->resultSet = Database::prepareResultSet(
                $databases['destination_db']['database'],
                $databases['destination_db']['username'],
                $databases['destination_db']['password'],
                $databases['destination_db']['host'],
                (int) $databases['destination_db']['port'],
                $this->data
            );
        }

        /** @var array<int|string>|false|null $row */
        $row = mysqli_fetch_assoc($this->resultSet);

        if ($row === false) {
            throw new ExecutionException("Something went wrong while retrieving an item from the destination.");
        }

        return $row;
    }
}
