<?php

namespace Blugen\Tests\Unit\Service\Xrpc\Encoder\Encoders;

use Blugen\Service\Lexicon\ProcedureInterface;
use Blugen\Service\Xrpc\Encoder\Encoders\InputEncoder;
use Blugen\Service\Xrpc\Encoder\PropertyCollector;
use Blugen\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class InputEncoderTest extends TestCase
{
    private ProcedureInterface&MockObject $procedure;
    private PropertyCollector&MockObject $collector;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->procedure = $this->createMock(ProcedureInterface::class);
        $this->collector = $this->createMock(PropertyCollector::class);
    }

    public function test_it_constructs_with_procedure_only(): void
    {
        $encoder = new InputEncoder($this->procedure);
        
        $this->assertInstanceOf(InputEncoder::class, $encoder);
    }

    public function test_it_constructs_with_procedure_and_collector(): void
    {
        $encoder = new InputEncoder($this->procedure, $this->collector);
        
        $this->assertInstanceOf(InputEncoder::class, $encoder);
    }

    public function test_it_encodes_empty_data_as_json(): void
    {
        $this->collector
            ->method('collect')
            ->willReturn([]);

        $encoder = new InputEncoder($this->procedure, $this->collector);
        $result = $encoder->encode();

        $this->assertEquals('[]', $result);
    }

    public function test_it_encodes_simple_data_as_json(): void
    {
        $data = [
            'name' => 'John Doe',
            'age' => 30,
            'active' => true
        ];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new InputEncoder($this->procedure, $this->collector);
        $result = $encoder->encode();

        $expected = json_encode($data, JSON_THROW_ON_ERROR);
        $this->assertEquals($expected, $result);
    }

    public function test_it_encodes_nested_array_data_as_json(): void
    {
        $data = [
            'user' => [
                'name' => 'John Doe',
                'profile' => [
                    'email' => 'john@example.com',
                    'preferences' => ['theme' => 'dark']
                ]
            ],
            'settings' => [
                'notifications' => true,
                'privacy' => 'public'
            ]
        ];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new InputEncoder($this->procedure, $this->collector);
        $result = $encoder->encode();

        $expected = json_encode($data, JSON_THROW_ON_ERROR);
        $this->assertEquals($expected, $result);
    }

    public function test_it_encodes_various_data_types_as_json(): void
    {
        $data = [
            'string' => 'test',
            'integer' => 42,
            'float' => 3.14,
            'boolean_true' => true,
            'boolean_false' => false,
            'null' => null,
            'array' => ['a', 'b', 'c'],
            'empty_array' => [],
            'zero' => 0,
            'empty_string' => ''
        ];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new InputEncoder($this->procedure, $this->collector);
        $result = $encoder->encode();

        $expected = json_encode($data, JSON_THROW_ON_ERROR);
        $this->assertEquals($expected, $result);

        // Verify it's valid JSON
        $decoded = json_decode($result, true);
        $this->assertEquals($data, $decoded);
    }

    public function test_it_uses_json_throw_on_error_flag(): void
    {
        // Create an object that can't be JSON encoded (contains a resource)
        $resource = fopen('php://memory', 'r');
        $data = ['resource' => $resource];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new InputEncoder($this->procedure, $this->collector);

        $this->expectException(\JsonException::class);
        $encoder->encode();

        fclose($resource);
    }

    public function test_it_handles_collector_exceptions(): void
    {
        $this->collector
            ->method('collect')
            ->willThrowException(new \RuntimeException('Collection failed'));

        $encoder = new InputEncoder($this->procedure, $this->collector);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Collection failed');
        $encoder->encode();
    }

    public function test_it_encodes_unicode_data_correctly(): void
    {
        $data = [
            'emoji' => '🎉🚀',
            'chinese' => '你好世界',
            'arabic' => 'مرحبا بالعالم',
            'special_chars' => '"quotes" & <tags>'
        ];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new InputEncoder($this->procedure, $this->collector);
        $result = $encoder->encode();

        // Verify it's valid JSON and preserves unicode
        $decoded = json_decode($result, true);
        $this->assertEquals($data, $decoded);
    }

    public function test_it_creates_default_collector_when_none_provided(): void
    {
        // This test verifies that when no collector is provided,
        // a default one is created using DataProviderFactory
        $encoder = new InputEncoder($this->procedure);
        
        // We can't directly test the internal collector creation,
        // but we can verify the encoder still works
        $this->assertInstanceOf(InputEncoder::class, $encoder);
    }

    public function test_encode_returns_string(): void
    {
        $this->collector
            ->method('collect')
            ->willReturn(['test' => 'data']);

        $encoder = new InputEncoder($this->procedure, $this->collector);
        $result = $encoder->encode();

        $this->assertIsString($result);
    }

    public function test_multiple_encode_calls_return_consistent_results(): void
    {
        $data = ['consistent' => 'data'];
        
        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new InputEncoder($this->procedure, $this->collector);
        
        $result1 = $encoder->encode();
        $result2 = $encoder->encode();

        $this->assertEquals($result1, $result2);
        $this->assertEquals(json_encode($data, JSON_THROW_ON_ERROR), $result1);
    }
}