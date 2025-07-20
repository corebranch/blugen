<?php

namespace Blugen\Tests\Unit\Service\Lexicon\V1;

use Blugen\Config\ConfigManager;
use Blugen\Service\Lexicon\V1\Lexicon;
use Blugen\Service\Lexicon\V1\Nsid;
use PHPUnit\Framework\Attributes\DataProvider;
use Blugen\Tests\TestCase;
use JsonException;
use Exception;
use TypeError;

class LexiconTest extends TestCase
{
    private function sampleLexiconArray(): array
    {
        return [
            'lexicon' => 1,
            'id' => 'app.user.profile',
            'description' => 'Sample user profile lexicon.',
            'defs' => [
                'main' => [
                    'type' => 'object',
                ],
                'usersList' => [
                    'type' => 'array',
                ],
            ],
        ];
    }

    private function sampleLexiconJson(): string
    {
        return json_encode($this->sampleLexiconArray(), JSON_THROW_ON_ERROR);
    }

    #[DataProvider('lexiconProvider')]
    public function test_it_accepts_json_and_array_on_construction(array $expectedArray, Lexicon $lexicon): void
    {
        $this->assertSame($expectedArray['id'], $lexicon->nsid());
        $this->assertSame($expectedArray['lexicon'], $lexicon->version());
        $this->assertSame($expectedArray['description'], $lexicon->description());
        $this->assertSame($expectedArray['defs'], $lexicon->defs());

        $this->assertIsString($lexicon->nsid());
        $this->assertIsInt($lexicon->version());
        $this->assertIsArray($lexicon->defs());
        $this->assertTrue(
            is_string($lexicon->description()) || is_null($lexicon->description())
        );
    }

    public static function lexiconProvider(): array
    {
        $array = [
            'lexicon' => 1,
            'id' => 'app.user.profile',
            'description' => 'Sample user profile lexicon.',
            'defs' => [
                'main' => ['type' => 'object'],
                'usersList' => ['type' => 'array'],
            ],
        ];

        return [
            'from array' => [$array, new Lexicon($array)],
            'from json'  => [$array, new Lexicon(json_encode($array, JSON_THROW_ON_ERROR))],
        ];
    }

    public function test_it_throws_json_exception_when_invalid_json_provided(): void
    {
        $this->expectException(JsonException::class);

        new Lexicon('invalid_json');
    }

    public function test_description_can_be_null_if_not_set(): void
    {
        $array = [
            'lexicon' => 1,
            'id' => 'app.minimal',
            'defs' => [],
        ];

        $lexicon = new Lexicon($array);

        $this->assertNull($lexicon->description());
    }

    public function test_fromNsid_parses_lexicon_from_a_nsid(): void
    {
        $nsid = new Nsid('app.bsky.actor.getProfile');
        $lexicon = Lexicon::fromNsid($nsid);

        $associativeArr = json_decode(
            file_get_contents(__DIR__."/../../../../../atproto/lexicons/app/bsky/actor/getProfile.json"),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertSame($associativeArr['lexicon'], $lexicon->version());
        $this->assertSame($associativeArr['id'], $lexicon->nsid());
        $this->assertSame($associativeArr['description'] ?? null, $lexicon->description());
        $this->assertSame($associativeArr['defs'], $lexicon->defs());
    }

    public function test_fromNsid_throws_not_found_exception_when_file_not_found(): void
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage("does not exist");

        $nsid = new Nsid('nonexistent.lexicon');
        Lexicon::fromNsid($nsid);
    }

    public function test_fromNsid_throws_invalid_json_exception_when_file_contains_invalid_json(): void
    {
        $this->expectException(JsonException::class);
        $this->expectExceptionMessage("does not contain valid JSON");

        $name = "temp_".uniqid();
        $tempPath = sys_get_temp_dir().DIRECTORY_SEPARATOR."$name.json";
        file_put_contents($tempPath, "invalid");

        container()->get(ConfigManager::class)->set('lexicons.source', dirname($tempPath));

        try {
            Lexicon::fromNsid(new Nsid($name));
        } catch (\Throwable $e) {
            unlink($tempPath);
            throw $e;
        }
    }

    public function test_construction_with_malformed_json_depth_exceeded(): void
    {
        $this->expectException(JsonException::class);
        
        $deeplyNested = str_repeat('[', 600) . '1' . str_repeat(']', 600);
        new Lexicon($deeplyNested);
    }

    public function test_construction_with_non_array_after_json_decode(): void
    {
        $this->expectException(TypeError::class);
        
        new Lexicon('"not-an-array"');
    }
}
