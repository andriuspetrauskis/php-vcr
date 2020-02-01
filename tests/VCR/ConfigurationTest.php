<?php

namespace VCR;

use PHPUnit\Framework\TestCase;
use VCR\LibraryHooks\StreamWrapperHook;
use VCR\LibraryHooks\CurlHook;
use VCR\LibraryHooks\SoapHook;
use VCR\RequestMatcher;
use VCR\Storage\Json;
use VCR\Storage\Yaml;
use VCR\Storage\AbstractStorage;

/**
 *
 */
class ConfigurationTest extends TestCase
{
    /**
     * @var Configuration
     */
    private $config;

    public function setUp(): void
    {
        $this->config = new Configuration;
    }

    public function testSetCassettePathThrowsErrorOnInvalidPath(): void
    {
        $this->expectException(
            VCRException::class,
            "Cassette path 'invalid_path' is not a directory. Please either "
            . 'create it or set a different cassette path using '
            . "\\VCR\\VCR::configure()->setCassettePath('directory')."
        );
        $this->config->setCassettePath('invalid_path');
    }

    public function testGetLibraryHooks(): void
    {
        $this->assertEquals(
            [
                StreamWrapperHook::class,
                CurlHook::class,
                SoapHook::class,
            ],
            $this->config->getLibraryHooks()
        );
    }

    public function testEnableLibraryHooks(): void
    {
        $this->config->enableLibraryHooks(['stream_wrapper']);
        $this->assertEquals(
            [
                StreamWrapperHook::class,
            ],
            $this->config->getLibraryHooks()
        );
    }

    public function testEnableSingleLibraryHook(): void
    {
        $this->config->enableLibraryHooks('stream_wrapper');
        $this->assertEquals(
            [
                StreamWrapperHook::class,
            ],
            $this->config->getLibraryHooks()
        );
    }

    public function testEnableLibraryHooksFailsWithWrongHookName(): void
    {
        $this->expectException(\InvalidArgumentException::class, "Library hooks don't exist: non_existing");
        $this->config->enableLibraryHooks(['non_existing']);
    }

    public function testEnableRequestMatchers(): void
    {
        $this->config->enableRequestMatchers(['body', 'headers']);
        $this->assertEquals(
            [
                [RequestMatcher::class, 'matchHeaders'],
                [RequestMatcher::class, 'matchBody'],
            ],
            $this->config->getRequestMatchers()
        );
    }

    public function testEnableRequestMatchersFailsWithNoExistingName(): void
    {
        $this->expectException(\InvalidArgumentException::class, "Request matchers don't exist: wrong, name");
        $this->config->enableRequestMatchers(['wrong', 'name']);
    }

    public function testAddRequestMatcherFailsWithNoName(): void
    {
        $this->expectException(VCRException::class, "A request matchers name must be at least one character long. Found ''");
        $expected = static function ($first, $second) {
            return true;
        };
        $this->config->addRequestMatcher('', $expected);
    }

    public function testAddRequestMatchers(): void
    {
        $expected = static function () {
            return true;
        };
        $this->config->addRequestMatcher('new_matcher', $expected);
        $this->assertContains($expected, $this->config->getRequestMatchers());
    }

    /**
     * @dataProvider availableStorageProvider
     */
    public function testSetStorage(string $name, string $className): void
    {
        $this->config->setStorage($name);
        $this->assertEquals($className, $this->config->getStorage(), "$name should be class $className.");
    }

    public function availableStorageProvider(): array
    {
        return [
            ['json', Json::class],
            ['yaml', Yaml::class],
        ];
    }

    public function testSetStorageInvalidName(): void
    {
        $this->expectException(VCRException::class, "Storage 'Does not exist' not available.");
        $this->config->setStorage('Does not exist');
    }

    public function testGetStorage(): void
    {
        $class = $this->config->getStorage();
        $this->assertContains('Iterator', class_implements($class));
        $this->assertContains('Traversable', class_implements($class));
        $this->assertContains(AbstractStorage::class, class_parents($class));
    }

    public function testWhitelist(): void
    {
        $expected = ['Tux', 'Gnu'];

        $this->config->setWhiteList($expected);

        $this->assertEquals($expected, $this->config->getWhiteList());
    }

    public function testBlacklist(): void
    {
        $expected = ['Tux', 'Gnu'];

        $this->config->setBlackList($expected);

        $this->assertEquals($expected, $this->config->getBlackList());
    }

    public function testSetModeInvalidName(): void
    {
        $this->expectException(VCRException::class, "Mode 'invalid' does not exist.");
        $this->config->setMode('invalid');
    }
}
