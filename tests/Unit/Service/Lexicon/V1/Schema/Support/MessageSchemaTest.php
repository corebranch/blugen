<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\TypeSpecificSchema\Support;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Meta\UnionSchema;
use Blugen\Service\Lexicon\V1\Schema\Support\MessageSchema;
use Blugen\Service\Lexicon\V1\Traits\ArrayableTrait;
use Blugen\Tests\TestCase;
use Blugen\Tests\Unit\Traits\WithGetTestTrait;
use Blugen\Tests\Unit\Traits\WithSchema;
use Blugen\Tests\Unit\Traits\WithSupportSchemaTestTrait;
use TypeError;

class MessageSchemaTest extends TestCase
{
    use WithSchema;
    use WithGetTestTrait;
    use ArrayableTrait;
    use WithSupportSchemaTestTrait;

    public function test_schema_returns_expected_value(): void
    {
        $schema = ['foo' => 'bar', 'baz' => 'qux'];
        $messageSchema = $this->schema(['schema' => $schema]);

        $this->assertInstanceOf(UnionSchema::class, $unionSchema = $messageSchema->schema());
        $this->assertSame($schema, $unionSchema->toArray());
    }

    public function test_schema_is_required(): void
    {
        $messageSchema = $this->schema([
            // missing schema
        ]);

        $this->expectException(TypeError::class);

        $messageSchema->schema();
    }

    public function test_toArray_works_expected(): void
    {
        $schema = [
            'description' => 'blah blah blah',
            'schema' => [
                'type' => 'union',
                'refs' => ['app.com.example', 'example.reverse.dns']
            ]
        ];

        $messageSchema = $this->schema($schema);

        $this->assertSame($schema, $messageSchema->toArray());
    }

    private function schema(array $content = []): MessageSchema
    {
        return new MessageSchema(new Schema($content));
    }
}
