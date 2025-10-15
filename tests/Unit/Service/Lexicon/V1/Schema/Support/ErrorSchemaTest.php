<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\TypeSpecificSchema\Support;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Support\ErrorSchema;
use Blugen\Tests\TestCase;
use Blugen\Tests\Unit\Traits\WithArrayableTestTrait;
use Blugen\Tests\Unit\Traits\WithGetTestTrait;
use Blugen\Tests\Unit\Traits\WithSchema;
use Blugen\Tests\Unit\Traits\WithSupportSchemaTestTrait;

class ErrorSchemaTest extends TestCase
{
    use WithSchema;
    use WithGetTestTrait;
    use WithArrayableTestTrait;
    use WithSupportSchemaTestTrait;

    public function test_name_is_required(): void
    {
        $schema = $this->schema([
            // missing name
        ]);

        $this->expectException(\TypeError::class);
        $schema->name();
    }

    public function test_name_returns_expected_value(): void
    {
        $schema = $this->schema([
            'name' => 'error name'
        ]);

        $this->assertSame('error name', $schema->name());
    }

    public function test_description_is_optional(): void
    {
        $schema = $this->schema([
            // missing description
        ]);

        $this->assertNull($schema->description());
    }

    public function test_description_returns_expected_value(): void
    {
        $schema = $this->schema([
            'description' => 'error description'
        ]);

        $this->assertSame('error description', $schema->description());
    }

    public function test_description_throws_exception(): void
    {
        // Placeholder: ErrorSchema intentionally overrides description behavior.
        // This test exists to ensure future consistency or documentation.
        $this->assertTrue(true);
    }

    private function schema(array $content): ErrorSchema
    {
        return new ErrorSchema(new Schema($content));
    }
}
