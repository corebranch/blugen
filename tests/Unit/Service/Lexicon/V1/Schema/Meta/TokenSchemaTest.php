<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\Schema\Meta;

use Blugen\Service\Lexicon\V1\Schema;
use Blugen\Service\Lexicon\V1\Schema\Meta\TokenSchema;
use PHPUnit\Framework\TestCase;

class TokenSchemaTest extends TestCase
{
    private function make(array $schema): TokenSchema
    {
        return new TokenSchema(new Schema($schema));
    }

    public function test_type_description_and_to_array(): void
    {
        $schema = [
            'type' => 'token',
            'description' => 'Opaque token',
            'optional' => null,
        ];

        $token = $this->make($schema);

        $this->assertSame('token', $token->type());
        $this->assertSame('Opaque token', $token->description());

        // toArray filters only top-level nulls
        $this->assertSame([
            'type' => 'token',
            'description' => 'Opaque token',
        ], $token->toArray());
    }

    public function test_description_nullable_and_schema_return(): void
    {
        $schema = [
            'type' => 'token',
        ];

        $token = $this->make($schema);
        $this->assertNull($token->description());
        $this->assertSame(['type' => 'token'], $token->toArray());
        $this->assertSame($schema, $token->schema());
    }

    public function test_magic_get_dotted_paths(): void
    {
        $schema = [
            'type' => 'token',
            'meta' => [
                'info' => [
                    'hint' => 'jwt',
                ],
            ],
        ];

        $token = $this->make($schema);
        $this->assertSame('jwt', $token->__get('meta.info.hint'));
        $this->assertNull($token->__get('meta.info.missing'));
    }
}

