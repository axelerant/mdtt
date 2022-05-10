<?php

declare(strict_types=1);

namespace Mdtt\Test\Definition\Validate\DataSource;

use Mdtt\DataSource\Database;
use Mdtt\Definition\Validate\DataSource\Validator;
use Mdtt\Exception\SetupException;
use Mdtt\Utility\DataSource\Json as JsonDataSourceUtility;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ValidatorTest extends TestCase
{
    use ProphecyTrait;
    private Validator $validator;

    protected function setUp(): void
    {
        $jsonDataSourceUtility = $this->prophesize(JsonDataSourceUtility::class);
        $this->validator = new Validator($jsonDataSourceUtility->reveal());
        parent::setUp();
    }

    /**
     * @dataProvider validateExceptionDataProvider
     */
    public function testValidateException(
        string $type,
        array $rawDataSourceDefinition,
        string $expectedExceptionMessage
    ): void {
        $this->expectException(SetupException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->validator->validate($type, $rawDataSourceDefinition);
    }

    public function testValidateDatabaseException(): void
    {
        $this->expectException(SetupException::class);
        $this->expectExceptionMessage('All information are not passed for database');

        $this->validator->validate('source', [
          "type" => "database",
        ]);
    }

    public function testValidateDatabase(): void
    {
        $datasource = $this->validator->validate('source', [
          "type" => "database",
          "database" => "source_db",
          "data" => "select * from users",
        ]);

        self::assertInstanceOf(Database::class, $datasource);
        self::assertSame('select * from users', $datasource->getData());
        self::assertSame('source_db', $datasource->getDatabaseKey());
    }

    public function testValidateJsonException(): void
    {
        $this->expectException(SetupException::class);
        $this->expectExceptionMessage("All information are not passed for json");

        $this->validator->validate('source', [
          'type' => 'json',
        ]);
    }

    public function validateExceptionDataProvider(): array
    {
        return [
          [
            "unknown_type",
            [
                "type" => "json",
                "data" => "http://localhost:8000",
                "selector" => "",
            ],
            "Incorrect data source type is passed.",
          ],
          [
            "source",
            [
              "type" => "unknown_type",
            ],
            "Unexpected data source type source and data source definition passed.",
          ]
        ];
    }
}
