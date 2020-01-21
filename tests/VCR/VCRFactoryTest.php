<?php

namespace VCR;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use VCR\Videorecorder;
use VCR\Configuration;
use VCR\Util\StreamProcessor;
use VCR\Util\HttpClient;
use VCR\CodeTransform\CurlCodeTransform;
use VCR\CodeTransform\SoapCodeTransform;
use VCR\LibraryHooks\CurlHook;
use VCR\LibraryHooks\SoapHook;
use VCR\LibraryHooks\StreamWrapperHook;
use VCR\Storage\Json;
use VCR\Storage\Yaml;

/**
 * Test instance creation.
 */
class VCRFactoryTest extends TestCase
{
    /**
     * @dataProvider instanceProvider
     * @covers VCR\VCRFactory::createVCRVideorecorder()
     */
    public function testCreateInstances($instance)
    {
        $this->assertInstanceOf($instance, VCRFactory::get($instance));
    }

    public function instanceProvider()
    {
        return [
            [Videorecorder::class],
            [Configuration::class],
            [StreamProcessor::class],
            [HttpClient::class],
            [CurlCodeTransform::class],
            [SoapCodeTransform::class],
            [CurlHook::class],
            [SoapHook::class],
            [StreamWrapperHook::class],
        ];
    }

    /**
     * @dataProvider storageProvider
     */
    public function testCreateStorage($storage, $className)
    {
        vfsStream::setup('test');

        VCRFactory::get(Configuration::class)->setStorage($storage);
        VCRFactory::get(Configuration::class)->setCassettePath(vfsStream::url('test/'));

        $instance = VCRFactory::get('Storage', [rand()]);

        $this->assertInstanceOf($className, $instance);
    }

    public function storageProvider()
    {
        return [
            ['json', Json::class],
            ['yaml', Yaml::class],
        ];
    }
}
