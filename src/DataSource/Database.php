<?php

declare(strict_types=1);

namespace Mdtt\DataSource;

use Iterator;
use Mdtt\Utility\DataSource\Database as DbDatabase;
use mysqli_result;

class Database extends DataSource
{
    private mysqli_result $resultSet;
    private string $database;
    private string $username;
    private string $password;
    private string $host;
    private int $port;

    public function __construct(
        string $data,
        string $database,
        string $username,
        string $password,
        string $host,
        int $port
    ) {
        parent::__construct($data);
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Iterator
    {
        if (!isset($this->resultSet)) {
            $this->resultSet = DbDatabase::prepareResultSet(
                $this->database,
                $this->username,
                $this->password,
                $this->host,
                $this->port,
                $this->data
            );
        }

        while ($row = $this->resultSet->fetch_assoc()) {
            yield $row;
        }
    }
}
