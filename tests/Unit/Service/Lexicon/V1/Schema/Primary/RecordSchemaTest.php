<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\Schema\Primary;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Container\ObjectSchema;
use Blugen\Service\Lexicon\V1\Schema\Primary\RecordSchema;
use Blugen\Tests\TestCase;
use Blugen\Tests\Unit\Traits\WithArrayableTestTrait;
use Blugen\Tests\Unit\Traits\WithGetTestTrait;
use Blugen\Tests\Unit\Traits\WithSchema;
use Blugen\Tests\Unit\Traits\WithSchemaTestTrait;

class RecordSchemaTest extends TestCase
{
    use WithSchema;
    use WithGetTestTrait;
    use WithSchemaTestTrait;
    use WithArrayableTestTrait;

    public function test_key_returns_expected_value(): void
    {
        $schema = $this->schema([
            'key' => 'tid'
        ]);

        $this->assertSame('tid', $schema->key());
        $this->assertSame(['key' => 'tid'], $schema->toArray());
    }

    public function test_key_is_required(): void
    {
        $schema = $this->schema([
            // missing key
        ]);

        $this->expectException(\TypeError::class);
        $schema->key();
    }

    public function test_record_returns_expected_value(): void
    {
        $schema = $this->schema([
            'record' => [
                'type' => 'object',
                'properties' => [
                    'field1' => [],
                    'field2' => [],
                ]
            ]
        ]);

        $this->assertInstanceOf(ObjectSchema::class, $objectSchema = $schema->record());
        $this->assertSame([
            'type' => 'object',
            'properties' => [
                'field1' => [],
                'field2' => [],
            ]
        ], $objectSchema->toArray());
    }

    public function test_record_is_required(): void
    {
        $schema = $this->schema([
            // missing record
        ]);

        $this->expectException(\TypeError::class);
        $schema->record();
    }

    private function schema(array $content): RecordSchema
    {
        return new RecordSchema(new Schema($content));
    }
}
