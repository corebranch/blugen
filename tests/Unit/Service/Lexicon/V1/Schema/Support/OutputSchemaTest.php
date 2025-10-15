<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\TypeSpecificSchema\Support;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Support\OutputSchema;
use Blugen\Tests\TestCase;
use Blugen\Tests\Unit\Traits\WithArrayableTestTrait;
use Blugen\Tests\Unit\Traits\WithGetTestTrait;
use Blugen\Tests\Unit\Traits\WithSchema;
use Blugen\Tests\Unit\Traits\WithSupportSchemaTestTrait;
use TypeError;

class OutputSchemaTest extends TestCase
{
    use WithSchema;
    use WithGetTestTrait;
    use WithArrayableTestTrait;
    use WithSupportSchemaTestTrait;

    public function test_description_throws_exception(): void
    {
        // Placeholder: OutputSchema has own description field
        $this->assertTrue(true);
    }

    public function test_description_returns_expected_value(): void
    {
        $schema = $this->schema(['description' => 'description of the output schema']);

        $this->assertSame('description of the output schema', $schema->description());
    }

    public function test_description_is_optional(): void
    {
        $schema = $this->schema([
            // missing description
        ]);

        $this->assertNull($schema->description());
    }

    public function test_encoding_returns_expected_value(): void
    {
        $schema = $this->schema([
            'encoding' => 'application/json',
        ]);

        $this->assertSame('application/json', $schema->encoding());
    }

    public function test_encoding_is_required(): void
    {
        $schema = $this->schema([
            // missing encoding
        ]);

        $this->expectException(TypeError::class);

        $schema->encoding();
    }

    public function test_schema_returns_expected_value(): void
    {
        $schema = ['type' => 'ref', 'ref' => 'com.example.schema'];
        $outputSchema = $this->schema(['schema' => $schema]);

        $this->assertSame($schema, $outputSchema->schema());
    }

    public function test_schema_is_optional(): void
    {
        $schema = $this->schema([
            'type' => 'output',
            // missing schema
        ]);

        $this->assertNull($schema->schema());
    }

    public function test_parameters_returns_expected_value(): void
    {
        $params = [
            'required' => ['field1', 'field2'],
            'properties' => [
                [
                    'type' => 'string',
                    'format' => 'did',
                ],
                [
                    'type' => 'array',
                    'items' => [
                        'type' => 'integer',
                        'minimum' => 30,
                        'maximum' => 100,
                    ]
                ]
            ],
        ];

        $schema = $this->schema([
            'parameters' => $params,
        ]);

        $this->assertSame($params, $schema->parameters());
    }

    public function test_parameters_is_optional(): void
    {
        $schema = $this->schema([
            // missing parameters
        ]);

        $this->assertNull($schema->parameters());
    }

    private function schema(array $content): OutputSchema
    {
        return new OutputSchema(new Schema($content));
    }

    public function test_toArray_delegates_to_schema_instance(): void
    {
        $expected = [
            'encoding' => 'application/json',
            'schema' => ['type' => 'ref', 'ref' => 'com.example'],
            'parameters' => ['foo' => 'bar'],
        ];

        $schema = $this->schema($expected);

        $this->assertSame($expected, $schema->toArray());
    }
}
