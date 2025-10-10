<?php

namespace Blugen\Tests\Unit\Service\Syntax\Factory;

use Blugen\Service\Syntax\Constraints\ArrayType\ArrayType;
use Blugen\Service\Syntax\Constraints\BooleanType\BooleanType;
use Blugen\Service\Syntax\Constraints\IntegerType\IntegerType;
use Blugen\Service\Syntax\Constraints\NullType\NullType;
use Blugen\Service\Syntax\Constraints\StringType\StringType;
use Blugen\Service\Syntax\Factory\ConstraintFactory;
use Blugen\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ConstraintFactoryTest extends TestCase
{
    #[DataProvider('supportedTypes')]
    public function test_can_create_constraint(string $type, string $expectedInstanceOf): void
    {
        $this->assertInstanceOf($expectedInstanceOf, ConstraintFactory::create($type, []));
    }

    public static function supportedTypes(): array
    {
        return [
            ['string', StringType::class],
            ['integer', IntegerType::class],
            ['boolean', BooleanType::class],
            ['array', ArrayType::class],
            ['null', NullType::class],
        ];
    }

    public function test_throws_exception_when_provided_unsupported_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Not found a constraint for type 'invalid-type'");

        ConstraintFactory::create('invalid-type', []);
    }

    public function test_passes_schema_to_constraint(): void
    {
        $expected = ['schema-item-key' => 'schema-item'];

        $this->assertSame($expected, ConstraintFactory::create('string', $expected)->schema());
    }
}
