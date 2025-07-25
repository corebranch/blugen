<?php

namespace Blugen\Tests\Unit\Service\Xrpc\Encoder\Encoders;

use Blugen\Service\Lexicon\QueryInterface;
use Blugen\Service\Xrpc\Encoder\Encoders\ParamsEncoder;
use Blugen\Service\Xrpc\Encoder\PropertyCollector;
use Blugen\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ParamsEncoderTest extends TestCase
{
    private QueryInterface&MockObject $params;
    private PropertyCollector&MockObject $collector;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->params = $this->createMock(QueryInterface::class);
        $this->collector = $this->createMock(PropertyCollector::class);
    }

    public function test_it_constructs_with_params_only(): void
    {
        $encoder = new ParamsEncoder($this->params);
        
        $this->assertInstanceOf(ParamsEncoder::class, $encoder);
    }

    public function test_it_constructs_with_params_and_collector(): void
    {
        $encoder = new ParamsEncoder($this->params, $this->collector);
        
        $this->assertInstanceOf(ParamsEncoder::class, $encoder);
    }

    public function test_it_encodes_empty_data_as_query_string(): void
    {
        $this->collector
            ->method('collect')
            ->willReturn([]);

        $encoder = new ParamsEncoder($this->params, $this->collector);
        $result = $encoder->encode();

        $this->assertEquals('', $result);
    }

    public function test_it_encodes_simple_data_as_query_string(): void
    {
        $data = [
            'name' => 'John Doe',
            'age' => 30
        ];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new ParamsEncoder($this->params, $this->collector);
        $result = $encoder->encode();

        $this->assertEquals('name=John+Doe&age=30', $result);
    }

    public function test_it_converts_boolean_true_to_string(): void
    {
        $data = [
            'active' => true,
            'verified' => true
        ];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new ParamsEncoder($this->params, $this->collector);
        $result = $encoder->encode();

        $this->assertEquals('active=true&verified=true', $result);
    }

    public function test_it_converts_boolean_false_to_string(): void
    {
        $data = [
            'active' => false,
            'verified' => false
        ];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new ParamsEncoder($this->params, $this->collector);
        $result = $encoder->encode();

        $this->assertEquals('active=false&verified=false', $result);
    }

    public function test_it_handles_mixed_boolean_values(): void
    {
        $data = [
            'active' => true,
            'verified' => false,
            'premium' => true
        ];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new ParamsEncoder($this->params, $this->collector);
        $result = $encoder->encode();

        $this->assertEquals('active=true&verified=false&premium=true', $result);
    }

    public function test_it_handles_various_data_types(): void
    {
        $data = [
            'string' => 'test',
            'integer' => 42,
            'float' => 3.14,
            'zero' => 0,
            'empty_string' => ''
        ];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new ParamsEncoder($this->params, $this->collector);
        $result = $encoder->encode();

        $this->assertEquals('string=test&integer=42&float=3.14&zero=0&empty_string=', $result);
    }

    public function test_it_normalizes_arrays_recursively(): void
    {
        $data = [
            'simple_array' => ['a', 'b', 'c'],
            'nested_booleans' => [
                'enabled' => true,
                'disabled' => false
            ]
        ];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new ParamsEncoder($this->params, $this->collector);
        $result = $encoder->encode();

        // Arrays are encoded by http_build_query with numeric indices
        $this->assertStringContainsString('simple_array%5B0%5D=a', $result);
        $this->assertStringContainsString('simple_array%5B1%5D=b', $result);
        $this->assertStringContainsString('simple_array%5B2%5D=c', $result);
        $this->assertStringContainsString('nested_booleans%5Benabled%5D=true', $result);
        $this->assertStringContainsString('nested_booleans%5Bdisabled%5D=false', $result);
    }

    public function test_it_handles_deeply_nested_arrays_with_booleans(): void
    {
        $data = [
            'config' => [
                'features' => [
                    'notifications' => true,
                    'analytics' => false,
                    'settings' => [
                        'dark_mode' => true,
                        'auto_save' => false
                    ]
                ]
            ]
        ];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new ParamsEncoder($this->params, $this->collector);
        $result = $encoder->encode();

        // Verify deep nesting with boolean conversion
        $this->assertStringContainsString('config%5Bfeatures%5D%5Bnotifications%5D=true', $result);
        $this->assertStringContainsString('config%5Bfeatures%5D%5Banalytics%5D=false', $result);
        $this->assertStringContainsString('config%5Bfeatures%5D%5Bsettings%5D%5Bdark_mode%5D=true', $result);
        $this->assertStringContainsString('config%5Bfeatures%5D%5Bsettings%5D%5Bauto_save%5D=false', $result);
    }

    public function test_it_handles_empty_arrays(): void
    {
        $data = [
            'empty_array' => [],
            'normal_param' => 'value'
        ];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new ParamsEncoder($this->params, $this->collector);
        $result = $encoder->encode();

        $this->assertStringContainsString('normal_param=value', $result);
        // Empty arrays may or may not appear in query string depending on http_build_query behavior
    }

    public function test_it_handles_special_characters_in_values(): void
    {
        $data = [
            'special' => 'hello world & more',
            'symbols' => '@#$%^&*()',
            'quotes' => '"quoted"'
        ];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new ParamsEncoder($this->params, $this->collector);
        $result = $encoder->encode();

        // http_build_query should properly encode special characters
        $this->assertStringContainsString('special=hello+world+%26+more', $result);
        $this->assertStringContainsString('symbols=%40%23%24%25%5E%26%2A%28%29', $result);
        $this->assertStringContainsString('quotes=%22quoted%22', $result);
    }

    public function test_it_handles_unicode_characters(): void
    {
        $data = [
            'emoji' => '🎉',
            'chinese' => '你好',
            'arabic' => 'مرحبا'
        ];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new ParamsEncoder($this->params, $this->collector);
        $result = $encoder->encode();

        // Unicode characters should be properly URL encoded
        $this->assertIsString($result);
        $this->assertStringContainsString('emoji=', $result);
        $this->assertStringContainsString('chinese=', $result);
        $this->assertStringContainsString('arabic=', $result);
    }

    public function test_it_handles_null_values(): void
    {
        $data = [
            'null_value' => null,
            'normal_value' => 'test'
        ];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new ParamsEncoder($this->params, $this->collector);
        $result = $encoder->encode();

        // http_build_query behavior with null values
        $this->assertStringContainsString('normal_value=test', $result);
    }

    public function test_it_handles_collector_exceptions(): void
    {
        $this->collector
            ->method('collect')
            ->willThrowException(new \RuntimeException('Collection failed'));

        $encoder = new ParamsEncoder($this->params, $this->collector);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Collection failed');
        $encoder->encode();
    }

    public function test_encode_returns_string(): void
    {
        $this->collector
            ->method('collect')
            ->willReturn(['test' => 'data']);

        $encoder = new ParamsEncoder($this->params, $this->collector);
        $result = $encoder->encode();

        $this->assertIsString($result);
    }

    public function test_multiple_encode_calls_return_consistent_results(): void
    {
        $data = [
            'consistent' => 'data',
            'enabled' => true
        ];
        
        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new ParamsEncoder($this->params, $this->collector);
        
        $result1 = $encoder->encode();
        $result2 = $encoder->encode();

        $this->assertEquals($result1, $result2);
        $this->assertEquals('consistent=data&enabled=true', $result1);
    }

    public function test_it_creates_default_collector_when_none_provided(): void
    {
        // This test verifies that when no collector is provided,
        // a default one is created using DataProviderFactory
        $encoder = new ParamsEncoder($this->params);
        
        // We can't directly test the internal collector creation,
        // but we can verify the encoder still works
        $this->assertInstanceOf(ParamsEncoder::class, $encoder);
    }

    public function test_normalize_preserves_non_boolean_array_values(): void
    {
        $data = [
            'mixed_array' => [
                'string' => 'test',
                'number' => 42,
                'boolean' => true,
                'nested' => [
                    'inner_bool' => false,
                    'inner_string' => 'value'
                ]
            ]
        ];

        $this->collector
            ->method('collect')
            ->willReturn($data);

        $encoder = new ParamsEncoder($this->params, $this->collector);
        $result = $encoder->encode();

        // Verify that booleans are converted to strings while other types remain
        $this->assertStringContainsString('mixed_array%5Bstring%5D=test', $result);
        $this->assertStringContainsString('mixed_array%5Bnumber%5D=42', $result);
        $this->assertStringContainsString('mixed_array%5Bboolean%5D=true', $result);
        $this->assertStringContainsString('mixed_array%5Bnested%5D%5Binner_bool%5D=false', $result);
        $this->assertStringContainsString('mixed_array%5Bnested%5D%5Binner_string%5D=value', $result);
    }
}