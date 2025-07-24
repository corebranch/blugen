<?php

namespace Blugen\Service\Lexicon\V1\DefGenerator\Primary;

use Blugen\Enum\ClassNameSuffix;
use Blugen\Service\Lexicon\GeneratorInterface;
use Blugen\Service\Lexicon\InputInterface;
use Blugen\Service\Lexicon\ProcedureInterface;
use Blugen\Service\Lexicon\V1\ComponentGenerator\Field\RefComponentGenerator;
use Blugen\Service\Lexicon\V1\Factory\ComponentGeneratorFactory;
use Blugen\Service\Lexicon\V1\Resolver\NamespaceResolver;
use Blugen\Service\Lexicon\V1\Resolver\NsidResolver;
use Blugen\Service\Lexicon\V1\TypeSpecificDefinition\Primary\ProcedureTypeDefinition;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\ObjectSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\RefSchema;
use Blugen\Service\Lexicon\V1\TypeSpecificSchema\Field\UnionSchema;
use Blugen\Service\Xrpc\CallableInterface;
use Blugen\Service\Xrpc\Encoder\Encoder;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Visibility;

class ProcedureGenerator implements GeneratorInterface
{
    private readonly PhpFile $file;
    private readonly PhpNamespace $namespace;
    private readonly ClassType $class;
    private readonly string $className;
    private readonly string $namespaceString;

    public function __construct(private readonly ProcedureTypeDefinition $definition)
    {
        [$this->namespaceString, $this->className] = NamespaceResolver::namespace($this->definition->lexicon(), $this->definition);

        $this->file = new PhpFile();
        $this->namespace = $this->file->addNamespace($this->namespaceString);
        $this->class = $this->namespace->addClass($this->className);

        $this->file->setStrictTypes();
    }

    public function generate(): array
    {
        $result = [];

        $this->class->addImplement(ProcedureInterface::class);

        $schema = $this->definition->input()?->schema();

        if ($schema instanceof UnionSchema) {
            $refs = array_map(function (string $ref) {
                $ref = NsidResolver::namespace($ref);

                $this->namespace->addUse($ref);

                return $ref;
            }, $schema->refs());

            $schemaClassName = implode("|", $refs);
        }

        if ($schema instanceof RefSchema) {
            $schemaClassName = NsidResolver::namespace($schema->ref());
            $this->namespace->addUse($schemaClassName);
        }

        if ($schema instanceof ObjectSchema) {
            $schemaFile = new PhpFile();
            $schemaFile->setStrictTypes();
            $schemaPhpNamespace = $schemaFile->addNamespace($this->namespaceString);
            $schemaClassName = "{$this->className}" . ClassNameSuffix::INPUT->value;
            $schemaNamespace = sprintf("%s\\%s", $schemaPhpNamespace->getName(), $schemaClassName);
            $schemaClass = $schemaPhpNamespace->addClass($schemaClassName);
            $schemaClass->addImplement(InputInterface::class);

            foreach($schema->properties() as $property) {
                ComponentGeneratorFactory::create($schemaClass, $property)->generate();
            }

            $this->class->addMethod("setSchema")
                ->setComment("@var \\$schemaNamespace \$schema")
                ->setPublic()
                ->setReturnType(new Literal("self"))
                ->addBody(new Literal("\$this->schema = \$schema;\nreturn \$this;"))
                ->addParameter("schema")
                ->setType(InputInterface::class);

            $this->class->addMethod("getSchema")
                ->setPublic()
                ->setReturnType($schemaNamespace)
                ->addBody(new Literal("return \$this->schema;"));

            $this->class->addImplement(CallableInterface::class);

            $this->class->addMethod('method')
                ->setReturnType('string')
                ->setBody("return 'POST';");

            $this->class->addMethod('path')
                ->setReturnType('string')
                ->setBody(sprintf("return '%s';", $this->definition->lexicon()->nsid()));

            $constructor = $this->class->addMethod('__construct');

            $constructor->addPromotedParameter('schema')
                ->setType($schemaNamespace)
                ->setVisibility(Visibility::Private);

            $this->class->addProperty('encoder')
                ->setType(Encoder::class)
                ->setReadOnly();

            $constructor->addParameter('encoder', null)
                ->setType(Encoder::class)
                ->setNullable();

            $constructor->setBody(sprintf(
                "\$this->encoder = \$encoder ?? new \\%s(\$this);",
                Encoder::class
            ));

            $this->class->addMethod('options')
                ->setReturnType('array')
                ->setBody("return ["
                    . "'body' => \$this->encoder->encode(),"
                    . "'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json']"
                . "];");

            $this->namespace->addUse($schemaNamespace);

            $result["$schemaClassName.php"] = $schemaFile->__toString();
        }

        $result["$this->className.php"] = $this->file->__toString();

        return $result;
    }
}
