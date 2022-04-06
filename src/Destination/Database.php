<?php

declare(strict_types=1);

namespace Mdtt\Destination;

use Iterator;
use Mdtt\DataSource;
use Mdtt\Exception\ExecutionException;
use Mdtt\Exception\SetupException;
use Mdtt\Utility\DataSource\Database as DbDatabase;
use mysqli_result;

class Database extends DataSource
{
    private mysqli_result $resultSet;
    private string $databaseKey;

    public function __construct(string $data, string $databaseKey)
    {
        parent::__construct($data);
        $this->databaseKey = $databaseKey;
    }

    /**
     * @inheritDoc
     */
    public function getItem(): Iterator
    {
        $specification = require "tests/mdtt/spec.php";

        /** @var array<array<string>> $databases */
        $databases = $specification['databases'];
        if (!isset(
            $databases[$this->databaseKey]['database'],
            $databases[$this->databaseKey]['username'],
            $databases[$this->databaseKey]['password'],
            $databases[$this->databaseKey]['host'],
            $databases[$this->databaseKey]['port']
        )) {
            throw new SetupException("Destination database not specified correctly");
        }

        if (!isset($this->resultSet)) {
            $this->resultSet = DbDatabase::prepareResultSet(
                $databases[$this->databaseKey]['database'],
                $databases[$this->databaseKey]['username'],
                $databases[$this->databaseKey]['password'],
                $databases[$this->databaseKey]['host'],
                (int) $databases[$this->databaseKey]['port'],
                $this->data
            );
        }

        /** @var Iterator|false $row */
        $row = mysqli_fetch_assoc($this->resultSet);

        if ($row === false) {
            throw new ExecutionException("Something went wrong while retrieving an item from the destination.");
        }

        return $row;
    }
}
