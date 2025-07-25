<?php

namespace Blugen\Tests\Unit\Service\Xrpc\Encoder;

use Blugen\Service\Lexicon\ProcedureInterface;
use Blugen\Service\Lexicon\QueryInterface;
use Blugen\Service\Xrpc\CallableInterface;
use Blugen\Service\Xrpc\Encoder\Encoders\InputEncoder;
use Blugen\Service\Xrpc\Encoder\Encoders\ParamsEncoder;
use Blugen\Service\Xrpc\Encoder\Factory;
use Blugen\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class FactoryTest extends TestCase
{
    #[DataProvider('expected_encoders')]
    public function test_it_creates_expected_encoder(string $callable, string $expected)
    {
        $callableMock = $this->createMockForIntersectionOfInterfaces([CallableInterface::class, $callable]);
        $factory = new Factory($callableMock);

        $this->assertInstanceOf($expected, $factory->create());
    }

    public static function expected_encoders(): array
    {
        return [
            ['callable' => ProcedureInterface::class, 'expected' => InputEncoder::class],
            ['callable' => QueryInterface::class, 'expected' => ParamsEncoder::class],
        ];
    }
}
