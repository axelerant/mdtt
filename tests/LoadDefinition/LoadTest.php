<?php

declare(strict_types=1);

namespace Mdtt\tests\LoadDefinition;

use Mdtt\Definition\Validate\DataSource\Validator;
use Mdtt\Exception\SetupException;
use Mdtt\LoadDefinition\DefaultLoader;
use Mdtt\LoadDefinition\Load;
use Mdtt\Transform\PluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;

class LoadTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $logger;
    private ObjectProphecy $validator;
    private ObjectProphecy $transformationPluginManager;
    private Load $defaultLoader;

    protected function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->validator = $this->prophesize(Validator::class);
        $this->transformationPluginManager = $this->prophesize(PluginManager::class);

        $this->defaultLoader = new DefaultLoader(
          $this->logger->reveal(),
          $this->validator->reveal(),
          $this->transformationPluginManager->reveal()
        );

        parent::setUp();
    }

    public function testScan(): void
    {
        $rawTestDefinitions = $this->defaultLoader->scan([
          "tests/fixtures/*.yml"
        ]);

        self::assertNotEmpty($rawTestDefinitions);
        self::assertCount(2, $rawTestDefinitions);
    }

    /**
     * @dataProvider validateExceptionDataProvider
     */
    public function testValidateException(array $testDefinition, string $exceptionMessage): void
    {
        $this->expectException(SetupException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->defaultLoader->validate($testDefinition);
    }

    public function validateExceptionDataProvider(): array
    {
        return [
          [
              [
                "tests/fixtures/negative/validate_public_apis_1.yml"
              ],
              "Test definition id is missing"
          ]
        ];
    }
}
