<?php

namespace Blugen\Service\Syntax\Factory;

use Blugen\Service\Lexicon\SchemaInterface;
use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\ArraySchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\BlobSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\BooleanSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\BytesSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\CidLinkSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\IntegerSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\NullSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\ObjectSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\RefSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\StringSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\TokenSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\UnionSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\UnknownSchema;

class SchemaFactory
{
    public static function create(string $type, array $schema): SchemaInterface
    {
        return new (match ($type) {
            'string' => StringSchema::class,
            'integer' => IntegerSchema::class,
            'boolean' => BooleanSchema::class,
            'array' => ArraySchema::class,
            'null' => NullSchema::class,
            'ref' => RefSchema::class,
            'blob' => BlobSchema::class,
            'bytes' => BytesSchema::class,
            'cid-link' => CidLinkSchema::class,
            'object' => ObjectSchema::class,
            'token' => TokenSchema::class,
            'union' => UnionSchema::class,
            'unknown' => UnknownSchema::class,

            default => throw new \InvalidArgumentException("Not found a type-specific schema for type '$type'"),
        })(new Schema($schema));
    }
}
