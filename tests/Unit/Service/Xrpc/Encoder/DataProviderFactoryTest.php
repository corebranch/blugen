<?php

namespace Blugen\Tests\Unit\Service\Xrpc\Encoder;

use Blugen\Service\Lexicon\ProcedureInterface;
use Blugen\Service\Lexicon\QueryInterface;
use Blugen\Service\Xrpc\Encoder\DataProviderFactory;
use Blugen\Service\Xrpc\Encoder\ProcedureDataAdapter;
use Blugen\Service\Xrpc\Encoder\QueryDataAdapter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DataProviderFactoryTest extends TestCase
{
    #[DataProvider('expected_adapters')]
    public function test_create_returns_expected_provider_instance_for_procedure(string $mock, string $expected): void
    {
        $mock = $this->createMock($mock);
        $dataProvider = DataProviderFactory::create($mock);

        $this->assertInstanceOf($expected, $dataProvider);
    }

    public static function expected_adapters(): array
    {
        return [
            ['mock' => ProcedureInterface::class, 'expected' => ProcedureDataAdapter::class],
            ['mock' => QueryInterface::class, 'expected' => QueryDataAdapter::class],
        ];
    }
}
