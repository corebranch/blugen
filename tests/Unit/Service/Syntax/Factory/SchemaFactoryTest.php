<?php

namespace Blugen\Tests\Unit\Service\Syntax\Factory;

use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\ArraySchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\BlobSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\BooleanSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\BytesSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\CidLinkSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\IntegerSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\NullSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\ObjectSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\StringSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\TokenSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\UnionSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\UnknownSchema;
use Blugen\Service\Syntax\Factory\SchemaFactory;
use Blugen\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class SchemaFactoryTest extends TestCase
{
    #[DataProvider('supportedTypes')]
    public function test_can_create_schema(string $type, string $expectedSchemaFQCN): void
    {
        $this->assertInstanceOf($expectedSchemaFQCN, SchemaFactory::create($type, []));
    }

    public static function supportedTypes(): \Generator
    {
        yield 'string schema' => ['string', StringSchema::class];
        yield 'integer schema' => ['integer', IntegerSchema::class];
        yield 'boolean schema' => ['boolean', BooleanSchema::class];
        yield 'array schema' => ['array', ArraySchema::class];
        yield 'null schema' => ['null', NullSchema::class];
        yield 'object schema' => ['object', ObjectSchema::class];
        yield 'blob schema' => ['blob', BlobSchema::class];
        yield 'bytes schema' => ['bytes', BytesSchema::class];
        yield 'cid-link schema' => ['cid-link', CidLinkSchema::class];
        yield 'token schema' => ['token', TokenSchema::class];
        yield 'union schema' => ['union', UnionSchema::class];
        yield 'unknown schema' => ['unknown', UnknownSchema::class];
    }

    public function test_throws_exception_for_invalid_schema_name(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Not found a type-specific schema for type 'invalid-schema-name'");

        SchemaFactory::create('invalid-schema-name', []);
    }

    public function test_passes_schema_arr_to_instance(): void
    {
        $expected = [
            'ref 1',
            'ref 0'
        ];

        $refSchema = SchemaFactory::create('union', ['type' => 'union', 'refs' => $expected]);

        $this->assertInstanceOf(
            UnionSchema::class,
            $refSchema,
            "Must be instance of " . UnionSchema::class
        );

        $this->assertSame($expected, $refSchema->refs());
    }
}
