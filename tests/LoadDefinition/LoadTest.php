<?php

declare(strict_types=1);

namespace Mdtt\tests\LoadDefinition;

use Mdtt\Definition\Validate\DataSource\Validator;
use Mdtt\LoadDefinition\DefaultLoader;
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

    protected function setUp(): void
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->validator = $this->prophesize(Validator::class);
        $this->transformationPluginManager = $this->prophesize(PluginManager::class);

        parent::setUp();
    }

    public function testScan(): void
    {
        $defaultLoader = new DefaultLoader(
            $this->logger->reveal(),
            $this->validator->reveal(),
            $this->transformationPluginManager->reveal()
        );

        $rawTestDefinitions = $defaultLoader->scan([
          "tests/fixtures/*.yml"
        ]);

        self::assertNotEmpty($rawTestDefinitions);
        self::assertCount(2, $rawTestDefinitions);
    }
}
