<?php

declare(strict_types=1);

namespace Mdtt\Test\Definition\Validate\DataSource;

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
          ]
        ];
    }
}
