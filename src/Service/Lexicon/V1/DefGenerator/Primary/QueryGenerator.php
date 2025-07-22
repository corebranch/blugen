<?php

namespace Blugen\Service\Lexicon\V1\DefGenerator\Primary;

use Blugen\Service\Lexicon\GeneratorInterface;
use Blugen\Service\Lexicon\ParamsInterface;
use Blugen\Service\Lexicon\QueryInterface;
use Blugen\Service\Lexicon\V1\Factory\ComponentGeneratorFactory;
use Blugen\Service\Lexicon\V1\Resolver\NamespaceResolver;
use Blugen\Service\Lexicon\V1\TypeSpecificDefinition\Primary\QueryTypeDefinition;
use Blugen\Service\Xrpc\CallableInterface;
use Blugen\Service\Xrpc\Encoder\Encoder;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Visibility;

class QueryGenerator implements GeneratorInterface
{
    private readonly PhpFile $file;
    private readonly PhpNamespace $namespace;
    private readonly ClassType $class;
    private readonly string $namespaceString;
    private readonly string $className;

    public function __construct(
        private readonly QueryTypeDefinition $definition
    )
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

        $this->class->addImplement(QueryInterface::class);

        $paramsFile = new PhpFile();
        $paramsFile->setStrictTypes();
        $paramsPhpNamespace = $paramsFile->addNamespace($this->namespaceString);
        $paramsClassName = "{$this->className}Params";
        $paramsNamespace = sprintf("%s\\%s", $paramsPhpNamespace->getName(), $paramsClassName);
        $paramsClass = $paramsPhpNamespace->addClass($paramsClassName);
        $paramsClass->addImplement(ParamsInterface::class);


        foreach ($this->definition->parameters()?->properties() ?? [] as $property) {
            ComponentGeneratorFactory::create($paramsClass, $property)->generate();
        }

//        $this->class->addProperty(new Literal("params"))
//            ->setPrivate()
//            ->setType($paramsNamespace);

        $this->class->addMethod(new Literal("setParams"))
            ->setComment("@var \\$paramsNamespace \$params")
            ->setPublic()
            ->setReturnType(new Literal("self"))
            ->addBody(new Literal("\$this->params = \$params;\nreturn \$this;"))
            ->addParameter(new Literal("params"))
            ->setType(ParamsInterface::class);

        $this->class->addMethod(new Literal("getParams"))
            ->setPublic()
            ->setReturnType($paramsNamespace)
            ->addBody(new Literal("return \$this->params;"));

        $this->class->addImplement(CallableInterface::class);

        $this->class->addMethod('method')
            ->setReturnType('string')
            ->setBody("return 'GET';");

        $this->class->addMethod('path')
            ->setReturnType('string')
            ->setBody(sprintf(
                "return '%s' . ((\$queryString = \$this->encoder->encode()) ? '?' . \$queryString : '');",
                $this->definition->lexicon()->nsid()
            ));

        $constructor = $this->class->addMethod('__construct');

        $constructor->addPromotedParameter('params')
            ->setType($paramsNamespace)
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
                . "'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json']"
            . "];");

        $this->namespace->addUse($paramsNamespace);

        return [
            "$this->className.php" => $this->file->__toString(),
            "$paramsClassName.php" => $paramsFile->__toString(),
        ];
    }
}
