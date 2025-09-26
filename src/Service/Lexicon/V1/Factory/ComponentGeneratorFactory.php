<?php

namespace Blugen\Service\Lexicon\V1\Factory;

use Blugen\Service\Lexicon\GeneratorInterface;
use Blugen\Service\Lexicon\LexiconInterface;
use Blugen\Service\Lexicon\V1\ComponentGenerator\Field\ArrayComponentGenerator;
use Blugen\Service\Lexicon\V1\ComponentGenerator\Field\BooleanComponentGenerator;
use Blugen\Service\Lexicon\V1\ComponentGenerator\Field\BytesComponentGenerator;
use Blugen\Service\Lexicon\V1\ComponentGenerator\Field\CidLinkComponentGenerator;
use Blugen\Service\Lexicon\V1\ComponentGenerator\Field\IntegerComponentGenerator;
use Blugen\Service\Lexicon\V1\ComponentGenerator\Field\NullComponentGenerator;
use Blugen\Service\Lexicon\V1\ComponentGenerator\Field\ObjectComponentGenerator;
use Blugen\Service\Lexicon\V1\ComponentGenerator\Field\ParamsComponentGenerator;
use Blugen\Service\Lexicon\V1\ComponentGenerator\Field\RefComponentGenerator;
use Blugen\Service\Lexicon\V1\ComponentGenerator\Field\StringComponentGenerator;
use Blugen\Service\Lexicon\V1\ComponentGenerator\Field\UnionComponentGenerator;
use Blugen\Service\Lexicon\V1\ComponentGenerator\Field\UnknownComponentGenerator;
use Blugen\Service\Lexicon\V1\Property;
use Nette\PhpGenerator\ClassType;
use Blugen\Service\Lexicon\V1\ComponentGenerator\Field\BlobComponentGenerator;
use Blugen\Service\Lexicon\V1\ComponentGenerator\Field\TokenComponentGenerator;

class ComponentGeneratorFactory
{
    public static function create(ClassType $class, Property $property, ?LexiconInterface $lexicon = null, ?GeneratorInterface $context = null): GeneratorInterface
    {
        $type = $property->schema()->type();

        return match($type) {
            'string' => new StringComponentGenerator($class, $property, $context),
            'object' => new ObjectComponentGenerator($class, $property, $context),
            'integer' => new IntegerComponentGenerator($class, $property, $context),
            'boolean' => new BooleanComponentGenerator($class, $property, $context),
            'array' => new ArrayComponentGenerator($class, $property, $context),
            'bytes' => new BytesComponentGenerator($class, $property, $context),
            'params' => new ParamsComponentGenerator($class, $property),
            'null' => new NullComponentGenerator($class, $property),
            'cid-link' => new CidLinkComponentGenerator($class, $property, $context),
            'blob' => new BlobComponentGenerator($class, $property, $context),
            'token' => new TokenComponentGenerator($class, $property),
            'ref' => new RefComponentGenerator($class, $property, $lexicon, $context),
            'union' => new UnionComponentGenerator($class, $property, $context),
            'unknown' => new UnknownComponentGenerator($class, $property, $context),
            default => throw new \RuntimeException("Unsupported type: $type"),
        };
    }
}
