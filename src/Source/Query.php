<?php

declare(strict_types=1);

namespace Mdtt\Source;

use Mdtt\Exception\SetupException;

class Query extends Source
{
    /**
     * @inheritDoc
     */
    public function processData(): array
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

        mysqli_query($databaseConnection, $this->data);

        return [];
    }
}
