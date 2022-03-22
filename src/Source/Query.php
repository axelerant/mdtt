<?php

declare(strict_types=1);

namespace Mdtt\Source;

use Mdtt\Exception\SetupException;

class Query extends Source
{
    public function __construct(string $data)
    {
        parent::__construct('query', $data);
    }

    /**
     * @inheritDoc
     */
    public function processData(): iterable
    {
        require_once "tests/mdtt/spec.php";

        if (!isset(
            $databases['source_db']['database'],
            $databases['source_db']['username'],
            $databases['source_db']['password'],
            $databases['source_db']['host'],
            $databases['source_db']['port']
        )) {
            throw new SetupException("Source database not specified correctly");
        }

        $databaseConnection = mysqli_connect(
            $databases['source_db']['host'],
            $databases['source_db']['username'],
            $databases['source_db']['password'],
            $databases['source_db']['database'],
            (int) $databases['source_db']['port']
        );

        mysqli_query($databaseConnection, $this->data);

        return [];
    }
}
