<?php

namespace Blugen\Tests\Unit\Config;

use Blugen\Config\ConfigManager;
use Blugen\Tests\TestCase;

class ConfigManagerTest extends TestCase
{
    public function testConstructorWithEmptyConfig(): void
    {
        $manager = new ConfigManager();
        
        $this->assertSame([], $manager->all());
    }

    public function testConstructorWithInitialConfig(): void
    {
        $config = ['app' => ['name' => 'Test App', 'debug' => true]];
        $manager = new ConfigManager($config);
        
        $this->assertSame($config, $manager->all());
    }

    public function testAllReturnsCompleteConfig(): void
    {
        $config = [
            'database' => ['host' => 'localhost', 'port' => 3306],
            'cache' => ['driver' => 'redis']
        ];
        $manager = new ConfigManager($config);
        
        $this->assertSame($config, $manager->all());
    }

    public function testGetSimpleKey(): void
    {
        $config = ['debug' => true, 'name' => 'Test App'];
        $manager = new ConfigManager($config);
        
        $this->assertTrue($manager->get('debug'));
        $this->assertSame('Test App', $manager->get('name'));
    }

    public function testGetNestedKeyWithDotNotation(): void
    {
        $config = [
            'database' => [
                'connections' => [
                    'mysql' => ['host' => 'localhost', 'port' => 3306]
                ]
            ]
        ];
        $manager = new ConfigManager($config);
        
        $this->assertSame('localhost', $manager->get('database.connections.mysql.host'));
        $this->assertSame(3306, $manager->get('database.connections.mysql.port'));
        $this->assertSame(['host' => 'localhost', 'port' => 3306], $manager->get('database.connections.mysql'));
    }

    public function testGetWithDefaultValue(): void
    {
        $manager = new ConfigManager(['existing' => 'value']);
        
        $this->assertSame('value', $manager->get('existing', 'default'));
        $this->assertSame('default', $manager->get('missing', 'default'));
        $this->assertNull($manager->get('missing'));
    }

    public function testGetNonExistentKey(): void
    {
        $manager = new ConfigManager(['key' => 'value']);
        
        $this->assertNull($manager->get('nonexistent'));
        $this->assertSame('default', $manager->get('nonexistent', 'default'));
    }

    public function testGetNonExistentNestedKey(): void
    {
        $config = ['level1' => ['level2' => 'value']];
        $manager = new ConfigManager($config);
        
        $this->assertNull($manager->get('level1.nonexistent'));
        $this->assertNull($manager->get('nonexistent.level2'));
        $this->assertSame('default', $manager->get('level1.nonexistent', 'default'));
    }

    public function testHasSimpleKey(): void
    {
        $config = ['existing' => 'value', 'null_value' => null];
        $manager = new ConfigManager($config);
        
        $this->assertTrue($manager->has('existing'));
        $this->assertTrue($manager->has('null_value'));
        $this->assertFalse($manager->has('nonexistent'));
    }

    public function testHasNestedKeyWithDotNotation(): void
    {
        $config = [
            'level1' => [
                'level2' => [
                    'level3' => 'value',
                    'null_value' => null
                ]
            ]
        ];
        $manager = new ConfigManager($config);
        
        $this->assertTrue($manager->has('level1.level2.level3'));
        $this->assertTrue($manager->has('level1.level2.null_value'));
        $this->assertTrue($manager->has('level1.level2'));
        $this->assertFalse($manager->has('level1.level2.nonexistent'));
        $this->assertFalse($manager->has('nonexistent.level2'));
    }

    public function testSetSimpleKey(): void
    {
        $manager = new ConfigManager();
        $manager->set('key', 'value');
        
        $this->assertSame('value', $manager->get('key'));
        $this->assertSame(['key' => 'value'], $manager->all());
    }

    public function testSetNestedKeyWithDotNotation(): void
    {
        $manager = new ConfigManager();
        $manager->set('level1.level2.level3', 'value');
        
        $this->assertSame('value', $manager->get('level1.level2.level3'));
        $this->assertSame(['level1' => ['level2' => ['level3' => 'value']]], $manager->all());
    }

    public function testSetOverwritesExistingValue(): void
    {
        $manager = new ConfigManager(['key' => 'original']);
        $manager->set('key', 'updated');
        
        $this->assertSame('updated', $manager->get('key'));
    }

    public function testSetCreatesIntermediateArrays(): void
    {
        $manager = new ConfigManager();
        $manager->set('a.b.c.d', 'value');
        
        $expected = ['a' => ['b' => ['c' => ['d' => 'value']]]];
        $this->assertSame($expected, $manager->all());
    }

    public function testSetOverwritesNonArrayWithArray(): void
    {
        $manager = new ConfigManager(['level1' => 'string_value']);
        $manager->set('level1.level2', 'new_value');
        
        $this->assertSame('new_value', $manager->get('level1.level2'));
        $this->assertSame(['level1' => ['level2' => 'new_value']], $manager->all());
    }

    public function testLoadFileExistingFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'config_test');
        file_put_contents($tempFile, "<?php return ['test' => 'value'];");
        
        $result = ConfigManager::loadFile($tempFile);
        
        $this->assertSame(['test' => 'value'], $result);
        
        unlink($tempFile);
    }

    public function testLoadFileNonExistentFile(): void
    {
        $result = ConfigManager::loadFile('/nonexistent/path/config.php');
        
        $this->assertSame([], $result);
    }

    public function testLoadMergesDefaultAndUserConfig(): void
    {
        $originalCwd = getcwd();
        $testDir = sys_get_temp_dir() . '/config_test_' . uniqid();
        mkdir($testDir);
        mkdir($testDir . '/config');
        
        file_put_contents($testDir . '/config/codegen.php', "<?php return ['user' => 'value', 'override' => 'user_value'];");
        
        chdir($testDir);
        
        $manager = ConfigManager::load();
        
        $this->assertTrue($manager->has('user'));
        $this->assertSame('value', $manager->get('user'));
        
        chdir($originalCwd);
        unlink($testDir . '/config/codegen.php');
        rmdir($testDir . '/config');
        rmdir($testDir);
    }

    public function testLoadWithoutUserConfig(): void
    {
        $originalCwd = getcwd();
        $testDir = sys_get_temp_dir() . '/config_test_empty_' . uniqid();
        mkdir($testDir);
        
        chdir($testDir);
        
        $manager = ConfigManager::load();
        
        $this->assertIsArray($manager->all());
        
        chdir($originalCwd);
        rmdir($testDir);
    }

    public function testLoadUserConfigFromMultiplePaths(): void
    {
        $originalCwd = getcwd();
        $testDir = sys_get_temp_dir() . '/config_test_paths_' . uniqid();
        mkdir($testDir);
        mkdir($testDir . '/config');
        mkdir($testDir . '/config/blugen');
        
        file_put_contents($testDir . '/config/blugen/codegen.php', "<?php return ['blugen_path' => true];");
        
        chdir($testDir);
        
        $manager = ConfigManager::load();
        
        $this->assertTrue($manager->get('blugen_path'));
        
        chdir($originalCwd);
        unlink($testDir . '/config/blugen/codegen.php');
        rmdir($testDir . '/config/blugen');
        rmdir($testDir . '/config');
        rmdir($testDir);
    }

    public function testGetWithEmptyKey(): void
    {
        $manager = new ConfigManager(['key' => 'value']);
        
        $this->assertNull($manager->get(''));
        $this->assertSame('default', $manager->get('', 'default'));
    }

    public function testHasWithEmptyKey(): void
    {
        $manager = new ConfigManager(['key' => 'value']);
        
        $this->assertFalse($manager->has(''));
    }

    public function testSetWithEmptyKey(): void
    {
        $manager = new ConfigManager();
        $manager->set('', 'value');
        
        $this->assertSame('value', $manager->get(''));
        $this->assertSame(['' => 'value'], $manager->all());
    }

    public function testGetWithVariousDataTypes(): void
    {
        $config = [
            'string' => 'text',
            'integer' => 42,
            'float' => 3.14,
            'boolean_true' => true,
            'boolean_false' => false,
            'null' => null,
            'array' => [1, 2, 3],
            'object' => (object) ['property' => 'value']
        ];
        $manager = new ConfigManager($config);
        
        $this->assertSame('text', $manager->get('string'));
        $this->assertSame(42, $manager->get('integer'));
        $this->assertSame(3.14, $manager->get('float'));
        $this->assertTrue($manager->get('boolean_true'));
        $this->assertFalse($manager->get('boolean_false'));
        $this->assertNull($manager->get('null'));
        $this->assertSame([1, 2, 3], $manager->get('array'));
        $this->assertEquals((object) ['property' => 'value'], $manager->get('object'));
    }

    public function testSetWithVariousDataTypes(): void
    {
        $manager = new ConfigManager();
        
        $object = (object) ['property' => 'value'];
        $manager->set('object', $object);
        $manager->set('array', [1, 2, 3]);
        $manager->set('null', null);
        $manager->set('boolean', false);
        
        $this->assertEquals($object, $manager->get('object'));
        $this->assertSame([1, 2, 3], $manager->get('array'));
        $this->assertNull($manager->get('null'));
        $this->assertFalse($manager->get('boolean'));
    }

    public function testGetWithKeysContainingDots(): void
    {
        $config = ['key.with.dots' => 'value'];
        $manager = new ConfigManager($config);
        
        $this->assertNull($manager->get('key.with.dots'));
        $this->assertSame('value', $manager->get('key.with.dots', 'value'));
    }

    public function testSetPreservesExistingStructure(): void
    {
        $manager = new ConfigManager([
            'level1' => [
                'existing' => 'value',
                'level2' => ['nested' => 'data']
            ]
        ]);
        
        $manager->set('level1.new_key', 'new_value');
        
        $this->assertSame('value', $manager->get('level1.existing'));
        $this->assertSame('data', $manager->get('level1.level2.nested'));
        $this->assertSame('new_value', $manager->get('level1.new_key'));
    }

    public function testGetWithDeepNesting(): void
    {
        $config = [
            'a' => ['b' => ['c' => ['d' => ['e' => 'deep_value']]]]
        ];
        $manager = new ConfigManager($config);
        
        $this->assertSame('deep_value', $manager->get('a.b.c.d.e'));
        $this->assertTrue($manager->has('a.b.c.d.e'));
    }

    public function testSetWithDeepNesting(): void
    {
        $manager = new ConfigManager();
        $manager->set('a.b.c.d.e.f.g', 'very_deep');
        
        $this->assertSame('very_deep', $manager->get('a.b.c.d.e.f.g'));
        $this->assertTrue($manager->has('a.b.c.d.e.f.g'));
    }

    public function testGetArrayTraversalStopsAtNonArray(): void
    {
        $config = [
            'level1' => [
                'level2' => 'string_value'
            ]
        ];
        $manager = new ConfigManager($config);
        
        $this->assertNull($manager->get('level1.level2.level3'));
        $this->assertSame('default', $manager->get('level1.level2.level3', 'default'));
    }

    public function testHasArrayTraversalStopsAtNonArray(): void
    {
        $config = [
            'level1' => [
                'level2' => 'string_value'
            ]
        ];
        $manager = new ConfigManager($config);
        
        $this->assertFalse($manager->has('level1.level2.level3'));
    }

    public function testMultipleConsecutiveDotsInKey(): void
    {
        $manager = new ConfigManager();
        $manager->set('a..b', 'value');
        
        $this->assertSame('value', $manager->get('a..b'));
        $this->assertTrue($manager->has('a..b'));
    }

    public function testKeyWithOnlyDots(): void
    {
        $manager = new ConfigManager();
        $manager->set('...', 'value');
        
        $this->assertSame('value', $manager->get('...'));
        $this->assertTrue($manager->has('...'));
    }

    public function testLoadFileWithMalformedPhp(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'config_test_malformed');
        file_put_contents($tempFile, "<?php syntax error here");
        
        $this->expectException(\ParseError::class);
        ConfigManager::loadFile($tempFile);
        
        unlink($tempFile);
    }

    public function testLoadFileWithNonArrayReturn(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'config_test_non_array');
        file_put_contents($tempFile, "<?php return 'not an array';");
        
        $this->expectException(\TypeError::class);
        ConfigManager::loadFile($tempFile);
        
        unlink($tempFile);
    }

    public function testConfigMutabilityAfterConstruction(): void
    {
        $originalConfig = ['key' => 'original'];
        $manager = new ConfigManager($originalConfig);
        
        $originalConfig['key'] = 'modified';
        
        $this->assertSame('original', $manager->get('key'));
    }

    public function testAllReturnsIndependentCopy(): void
    {
        $manager = new ConfigManager(['key' => 'value']);
        $config = $manager->all();
        $config['new_key'] = 'new_value';
        
        $this->assertFalse($manager->has('new_key'));
        $this->assertArrayNotHasKey('new_key', $manager->all());
    }
}
