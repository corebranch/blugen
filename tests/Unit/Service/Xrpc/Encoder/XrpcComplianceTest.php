<?php

namespace Blugen\Tests\Unit\Service\Xrpc\Encoder;

use Blugen\Service\Lexicon\InputInterface;
use Blugen\Service\Lexicon\ParamsInterface;
use Blugen\Service\Lexicon\ProcedureInterface;
use Blugen\Service\Lexicon\QueryInterface;
use Blugen\Service\Xrpc\Encoder\DataProviderFactory;
use Blugen\Service\Xrpc\Encoder\Encoders\InputEncoder;
use Blugen\Service\Xrpc\Encoder\Encoders\ParamsEncoder;
use Blugen\Service\Xrpc\Encoder\PropertyCollector;
use PHPUnit\Framework\TestCase;

class XrpcComplianceTest extends TestCase
{
    public function test_params_encoder_converts_boolean_false_to_false_string(): void
    {
        $params = new class implements ParamsInterface {
            public bool $active = false;
            public string $name = 'test';
            public int $count = 0;
            public string $empty = '';
        };

        $query = $this->createMock(QueryInterface::class);
        $query->method('getParams')->willReturn($params);

        $encoder = new ParamsEncoder($query);
        $result = $encoder->encode();

        // Parse the query string to verify boolean conversion
        parse_str($result, $decoded);

        $this->assertEquals('false', $decoded['active']);
        $this->assertEquals('test', $decoded['name']);
        $this->assertEquals('0', $decoded['count']);
        $this->assertEquals('', $decoded['empty']);
    }

    public function test_params_encoder_converts_boolean_true_to_true_string(): void
    {
        $params = new class implements ParamsInterface {
            public bool $active = true;
            public string $name = 'test';
        };

        $query = $this->createMock(QueryInterface::class);
        $query->method('getParams')->willReturn($params);

        $encoder = new ParamsEncoder($query);
        $result = $encoder->encode();

        parse_str($result, $decoded);

        $this->assertEquals('true', $decoded['active']);
        $this->assertEquals('test', $decoded['name']);
    }

    public function test_input_encoder_preserves_all_falsy_values_except_null(): void
    {
        $input = new class implements InputInterface {
            public bool $active = false;
            public int $count = 0;
            public string $empty = '';
            public array $emptyArray = [];
            public string $name = 'test';
            public ?string $nullValue = null;
        };

        $procedure = $this->createMock(ProcedureInterface::class);
        $procedure->method('getSchema')->willReturn($input);

        $encoder = new InputEncoder($procedure);
        $result = $encoder->encode();
        $decoded = json_decode($result, true);

        $this->assertFalse($decoded['active']);
        $this->assertEquals(0, $decoded['count']);
        $this->assertEquals('', $decoded['empty']);
        $this->assertEquals([], $decoded['emptyArray']);
        $this->assertEquals('test', $decoded['name']);
        $this->assertArrayNotHasKey('nullValue', $decoded);
    }

    public function test_property_collector_properly_filters_only_null_values(): void
    {
        $data = new class implements ParamsInterface {
            public bool $false = false;
            public int $zero = 0;
            public string $empty = '';
            public array $emptyArray = [];
            public ?string $null = null;
            public string $value = 'keep';
        };

        $dataProvider = DataProviderFactory::create(
            $this->createConfiguredMock(QueryInterface::class, [
                'getParams' => $data
            ])
        );

        $collector = new PropertyCollector($dataProvider);
        $result = $collector->collect();

        // All falsy values should be preserved except null
        $this->assertArrayHasKey('false', $result);
        $this->assertArrayHasKey('zero', $result);
        $this->assertArrayHasKey('empty', $result);
        $this->assertArrayHasKey('emptyArray', $result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayNotHasKey('null', $result);

        $this->assertFalse($result['false']);
        $this->assertEquals(0, $result['zero']);
        $this->assertEquals('', $result['empty']);
        $this->assertEquals([], $result['emptyArray']);
        $this->assertEquals('keep', $result['value']);
    }

    public function test_xrpc_boolean_parameter_specification_compliance(): void
    {
        // Test case based on XRPC spec: "When encoding boolean parameters, 
        // the strings 'true' and 'false' should be used"
        
        $params = new class implements ParamsInterface {
            public bool $includeReplies = false;
            public bool $includeRetweets = true;
            public ?bool $includeQuotes = null; // Should be filtered out
        };

        $query = $this->createConfiguredMock(QueryInterface::class, [
            'getParams' => $params
        ]);

        $encoder = new ParamsEncoder($query);
        $queryString = $encoder->encode();
        
        $this->assertStringContainsString('includeReplies=false', $queryString);
        $this->assertStringContainsString('includeRetweets=true', $queryString);
        $this->assertStringNotContainsString('includeQuotes', $queryString);
    }
}