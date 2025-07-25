<?php

namespace Blugen\Tests\Unit\Service\Xrpc\Encoder;

use Blugen\Service\Lexicon\InputInterface;
use Blugen\Service\Lexicon\ParamsInterface;
use Blugen\Service\Xrpc\Encoder\PropertyCollector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PropertyCollectorTest extends TestCase
{
    private PropertyCollector $propertyCollector;
    private \Blugen\Service\Xrpc\Encoder\DataProvider&MockObject $dataProvider;

    protected function setUp(): void
    {
        $this->dataProvider = $this->createMock(\Blugen\Service\Xrpc\Encoder\DataProvider::class);
        $this->propertyCollector = new PropertyCollector($this->dataProvider);
    }

    public function test_collect_returns_empty_array_for_object_with_no_properties(): void
    {
        $data = new class implements ParamsInterface {};
        
        $this->dataProvider
            ->method('getData')
            ->willReturn($data);

        $result = $this->propertyCollector->collect();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_collect_returns_initialized_properties(): void
    {
        $data = new class implements ParamsInterface {
            public string $name = 'test';
            public int $age = 25;
            public bool $active = true;
        };

        $this->dataProvider
            ->method('getData')
            ->willReturn($data);

        $result = $this->propertyCollector->collect();

        $expected = [
            'name' => 'test',
            'age' => 25,
            'active' => true,
        ];

        $this->assertEquals($expected, $result);
    }

    public function test_collect_filters_out_uninitialized_properties(): void
    {
        $data = new class implements InputInterface {
            public string $initialized = 'value';
            public string $uninitialized;
        };

        $this->dataProvider
            ->method('getData')
            ->willReturn($data);

        $result = $this->propertyCollector->collect();

        $this->assertEquals(['initialized' => 'value'], $result);
        $this->assertArrayNotHasKey('uninitialized', $result);
    }

    public function test_collect_filters_out_null_values_from_initialized_properties(): void
    {
        $data = new class implements ParamsInterface {
            public ?string $nullValue = null;
            public string $stringValue = 'test';
        };

        $this->dataProvider
            ->method('getData')
            ->willReturn($data);

        $result = $this->propertyCollector->collect();

        $expected = [
            'stringValue' => 'test',
        ];

        $this->assertEquals($expected, $result);
        $this->assertArrayNotHasKey('nullValue', $result);
    }

    #[DataProvider('variousDataTypesProvider')]
    public function test_collect_handles_various_data_types($value, $expectedValue): void
    {
        $data = new class($value) implements ParamsInterface {
            public function __construct(public $testProperty) {}
        };

        $this->dataProvider
            ->method('getData')
            ->willReturn($data);

        $result = $this->propertyCollector->collect();

        $this->assertEquals(['testProperty' => $expectedValue], $result);
    }

    public static function variousDataTypesProvider(): array
    {
        return [
            'string' => ['string_value', 'string_value'],
            'integer' => [42, 42],
            'float' => [3.14, 3.14],
            'boolean_true' => [true, true],
            'array' => [['a', 'b', 'c'], ['a', 'b', 'c']],
            'object' => [(object)['key' => 'value'], (object)['key' => 'value']],
        ];
    }

    public function test_collect_works_with_mixed_property_states(): void
    {
        $data = new class implements InputInterface {
            public string $initialized = 'value';
            public ?string $nullInitialized = null;
            public int $intValue = 100;
            public string $uninitialized;
            public ?int $uninitializedNullable;
        };

        $this->dataProvider
            ->method('getData')
            ->willReturn($data);

        $result = $this->propertyCollector->collect();

        $expected = [
            'initialized' => 'value',
            'intValue' => 100,
        ];

        $this->assertEquals($expected, $result);
        $this->assertArrayNotHasKey('uninitialized', $result);
        $this->assertArrayNotHasKey('uninitializedNullable', $result);
        $this->assertArrayNotHasKey('nullInitialized', $result);
    }

    public function test_collect_filters_out_only_null_values(): void
    {
        $data = new class implements ParamsInterface {
            public ?string $nullValue = null;
            public bool $falseValue = false;
            public int $zeroValue = 0;
            public string $emptyString = '';
            public array $emptyArray = [];
            public string $truthyValue = 'keep_me';
            public int $truthyInt = 1;
        };

        $this->dataProvider
            ->method('getData')
            ->willReturn($data);

        $result = $this->propertyCollector->collect();

        $expected = [
            'truthyValue' => 'keep_me',
            'truthyInt' => 1,
            'falseValue' => false,
            'zeroValue' => 0,
            'emptyString' => '',
            'emptyArray' => [],
        ];

        $this->assertEquals($expected, $result);
        $this->assertArrayNotHasKey('nullValue', $result);
        $this->assertArrayHasKey('falseValue', $result);
        $this->assertArrayHasKey('zeroValue', $result);
        $this->assertArrayHasKey('emptyString', $result);
        $this->assertArrayHasKey('emptyArray', $result);
    }

    public function test_collect_handles_private_and_protected_properties(): void
    {
        $data = new class implements ParamsInterface {
            public string $publicProp = 'public';
            protected string $protectedProp = 'protected';
            private string $privateProp = 'private';
        };

        $this->dataProvider
            ->method('getData')
            ->willReturn($data);

        $result = $this->propertyCollector->collect();

        $expected = [
            'publicProp' => 'public',
            'protectedProp' => 'protected',
            'privateProp' => 'private',
        ]; 

        $this->assertEquals($expected, $result);
    }

    public function test_collect_handles_static_properties(): void
    {
        $data = new class implements InputInterface {
            public static string $staticProp = 'static';
            public string $instanceProp = 'instance';
        };

        $this->dataProvider
            ->method('getData')
            ->willReturn($data);

        $result = $this->propertyCollector->collect();

        // Static properties should be included if they're part of the reflection
        $this->assertArrayHasKey('instanceProp', $result);
        $this->assertEquals('instance', $result['instanceProp']);
        
        // Static properties behavior depends on PHP reflection behavior
        if (array_key_exists('staticProp', $result)) {
            $this->assertEquals('static', $result['staticProp']);
        }
    }

    public function test_collect_works_with_property_promotion(): void
    {
        $data = new class('test', 42) implements ParamsInterface {
            public function __construct(
                public string $name,
                public int $value,
            ) {}
        };

        $this->dataProvider
            ->method('getData')
            ->willReturn($data);

        $result = $this->propertyCollector->collect();

        $expected = [
            'name' => 'test',
            'value' => 42,
        ];

        $this->assertEquals($expected, $result);
    }

    public function test_collect_returns_consistent_results_on_multiple_calls(): void
    {
        $data = new class implements ParamsInterface {
            public string $consistent = 'value';
        };

        $this->dataProvider
            ->method('getData')
            ->willReturn($data);

        $result1 = $this->propertyCollector->collect();
        $result2 = $this->propertyCollector->collect();

        $this->assertEquals($result1, $result2);
        $this->assertEquals(['consistent' => 'value'], $result1);
    }
}
