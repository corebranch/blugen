<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1\Resolver;

use Blugen\Config\ConfigManager;
use Blugen\Service\Lexicon\V1\Resolver\NamespaceResolver;
use Blugen\Service\Lexicon\LexiconInterface;
use Blugen\Service\Lexicon\DefinitionInterface;
use Blugen\Tests\TestCase;

class NamespaceResolverTest extends TestCase
{
    private NamespaceResolver $resolver;
    private LexiconInterface $lexiconMock;
    private DefinitionInterface $definitionMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Set empty base namespace for tests using existing ConfigManager
        config()->set('output.base_namespace', '');

        $this->resolver = new NamespaceResolver();
        $this->lexiconMock = $this->createMock(LexiconInterface::class);
        $this->definitionMock = $this->createMock(DefinitionInterface::class);
    }

    public function test_namespace_generates_correct_namespace_and_classname_for_non_main_definition(): void
    {
        $this->lexiconMock
            ->method('nsid')
            ->willReturn('app.user.profile');

        $this->lexiconMock
            ->method('defs')
            ->willReturn([
                'UserProfile' => ['type' => 'object']
            ]);

        $this->definitionMock
            ->method('name')
            ->willReturn('UserProfile');

        $this->definitionMock
            ->method('type')
            ->willReturn('object');

        [$namespace, $className] = $this->resolver->namespace($this->lexiconMock, $this->definitionMock);

        $this->assertSame('App\\User\\Profile', $namespace);
        $this->assertSame('UserProfile', $className);
    }

    public function test_namespace_uses_last_nsid_part_for_main_definition(): void
    {
        $this->lexiconMock
            ->method('nsid')
            ->willReturn('app.bsky.embed.record');

        $this->lexiconMock
            ->method('defs')
            ->willReturn([
                'main' => ['type' => 'object']
            ]);

        $this->definitionMock
            ->method('name')
            ->willReturn('main');

        $this->definitionMock
            ->method('type')
            ->willReturn('object');

        [$namespace, $className] = $this->resolver->namespace($this->lexiconMock, $this->definitionMock);

        $this->assertSame('App\\Bsky\\Embed', $namespace);
        $this->assertSame('Record', $className);
    }

    public function test_path_generates_correct_filesystem_path_for_main_definition(): void
    {
        $this->lexiconMock
            ->method('nsid')
            ->willReturn('app.bsky.feed.post');

        $this->lexiconMock
            ->method('defs')
            ->willReturn([
                'main' => ['type' => 'record']
            ]);

        $this->definitionMock
            ->method('name')
            ->willReturn('main');

        $this->definitionMock
            ->method('type')
            ->willReturn('record');

        $path = $this->resolver->path($this->lexiconMock, $this->definitionMock);

        $this->assertSame('App/Bsky/Feed/Post.php', $path);
    }

    public function test_path_generates_correct_filesystem_path_for_non_main_definition(): void
    {
        $this->lexiconMock
            ->method('nsid')
            ->willReturn('app.bsky.embed.record');

        $this->lexiconMock
            ->method('defs')
            ->willReturn([
                'view' => ['type' => 'object']
            ]);

        $this->definitionMock
            ->method('name')
            ->willReturn('view');

        $this->definitionMock
            ->method('type')
            ->willReturn('object');

        $path = $this->resolver->path($this->lexiconMock, $this->definitionMock);

        $this->assertSame('App/Bsky/Embed/Record/View.php', $path);
    }

    public function test_it_throws_exception_if_definition_does_not_exist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Definition "MissingDef" does not exist in the lexicon.');

        $this->lexiconMock
            ->method('nsid')
            ->willReturn('app.error.test');

        $this->lexiconMock
            ->method('defs')
            ->willReturn([
                // No definitions intentionally
            ]);

        $this->definitionMock
            ->method('name')
            ->willReturn('MissingDef');

        $this->resolver->namespace($this->lexiconMock, $this->definitionMock);
    }
}
