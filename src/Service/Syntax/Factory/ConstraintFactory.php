<?php

namespace Blugen\Service\Syntax\Factory;

use Blugen\Service\Syntax\Constraints\ArrayType\ArrayType;
use Blugen\Service\Syntax\Constraints\BooleanType\BooleanType;
use Blugen\Service\Syntax\Constraints\IntegerType\IntegerType;
use Blugen\Service\Syntax\Constraints\LexConstraint;
use Blugen\Service\Syntax\Constraints\NullType\NullType;
use Blugen\Service\Syntax\Constraints\StringType\StringType;

class ConstraintFactory
{
    public static function create(string $type, array $schema): LexConstraint
    {
        return new (match ($type) {
            'string' => StringType::class,
            'integer' => IntegerType::class,
            'boolean' => BooleanType::class,
            'array' => ArrayType::class,
            'null' => NullType::class,

            default => throw new \InvalidArgumentException("Not found a constraint for type '{$type}'"),
        })($schema);
    }
}
