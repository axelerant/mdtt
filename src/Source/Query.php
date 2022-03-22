<?php

declare(strict_types=1);

namespace Mdtt\Source;

use Mdtt\Exception\ExecutionException;
use Mdtt\Exception\SetupException;

class Query extends Source
{
    private \mysqli_result $resultSet;

    /**
     * Obtains the result set from the source database based on the query.
     */
    private function prepareResultSet(): void
    {
        $specification = require "tests/mdtt/spec.php";

        /** @var array<array<string>> $databases */
        $databases = $specification['databases'];
        if (!isset(
            $databases['source_db']['database'],
            $databases['source_db']['username'],
            $databases['source_db']['password'],
            $databases['source_db']['host'],
            $databases['source_db']['port']
        )) {
            throw new SetupException("Source database not specified correctly");
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            /** @var \mysqli $databaseConnection */
            $databaseConnection = mysqli_connect(
                $databases['source_db']['host'],
                $databases['source_db']['username'],
                $databases['source_db']['password'],
                $databases['source_db']['database'],
                (int) $databases['source_db']['port']
            );
        } catch (\Exception $exception) {
            throw new SetupException($exception->getMessage());
        }

        /** @var \mysqli_result|false $result */
        $result = mysqli_query($databaseConnection, $this->data);

        if ($result === false) {
            throw new ExecutionException("Something went wrong while retrieving data from source database.");
        }

        $this->resultSet = $result;
    }

    /**
     * @inheritDoc
     */
    public function getItem(): ?array
    {
        if (!isset($this->resultSet)) {
            $this->prepareResultSet();
        }

        /** @var array<int|string>|false|null $row */
        $row = mysqli_fetch_assoc($this->resultSet);

        if ($row === false) {
            throw new ExecutionException("Something went wrong while retrieving an item from the source.");
        }

        return $row;
    }
}
