<?php

declare(strict_types=1);

namespace Mdtt\Utility\DataSource;

use Mdtt\Exception\ExecutionException;
use Mdtt\Exception\SetupException;

class Database
{
    /**
     * Obtains the result set from a database based on the query.
     *
     * @param string $database
     * @param string $username
     * @param string $password
     * @param string $host
     * @param int $port
     * @param string $query
     *
     * @return \mysqli_result
     */
    public static function prepareResultSet(
        string $database,
        string $username,
        string $password,
        string $host,
        int $port,
        string $query
    ): \mysqli_result {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            /** @var \mysqli $databaseConnection */
            $databaseConnection = mysqli_connect(
                $host,
                $username,
                $password,
                $database,
                $port
            );
        } catch (\Exception $exception) {
            throw new SetupException($exception->getMessage());
        }

        /** @var \mysqli_result|false $result */
        $result = mysqli_query($databaseConnection, $query);

        if ($result === false) {
            throw new ExecutionException("Something went wrong while retrieving data from source database.");
        }

        return $result;
    }
}
