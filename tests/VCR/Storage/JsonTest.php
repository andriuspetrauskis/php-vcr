<?php

namespace VCR\Storage;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Test integration of PHPVCR with PHPUnit.
 */
class JsonTest extends TestCase
{
    protected $handle;
    protected $filePath;
    protected $jsonObject;

    public function setUp(): void
    {
        vfsStream::setup('test');
        $this->filePath = vfsStream::url('test/') . 'json_test';
        $this->jsonObject = new Json(vfsStream::url('test/'), 'json_test');
    }

    public function testIterateOneObject(): void
    {
        $this->iterateAndTest(
            '[{"para1": "val1"}]',
            [
                new Recording(['para1' => 'val1']),
            ],
            'Single json object was not parsed correctly.'
        );
    }

    public function testIterateTwoObjects(): void
    {
        $this->iterateAndTest(
            '[{"para1": "val1"}, {"para2": "val2"}]',
            [
                new Recording(['para1' => 'val1']),
                new Recording(['para2' => 'val2']),
            ],
            'Two json objects were not parsed correctly.'
        );
    }

    public function testIterateFirstNestedObject(): void
    {
        $this->iterateAndTest(
            '[{"para1": {"para2": "val2"}}, {"para3": "val3"}]',
            [
                new Recording(['para1' => ['para2' => 'val2']]),
                new Recording(['para3' => 'val3']),
            ],
            'Nested json objects were not parsed correctly.'
        );
    }

    public function testIterateSecondNestedObject(): void
    {
        $this->iterateAndTest(
            '[{"para1": "val1"}, {"para2": {"para3": "val3"}}]',
            [
                new Recording(['para1' => 'val1']),
                new Recording(['para2' => ['para3' => 'val3']]),
            ],
            'Nested json objects were not parsed correctly.'
        );
    }

    public function testIterateEmpty(): void
    {
        $this->iterateAndTest(
            '[]',
            [],
            'Empty json was not parsed correctly.'
        );
    }

    public function testStoreRecording(): void
    {
        $initialRecording = Recording::fromRequestAndResponseArray(
            [
                'some request'
            ],
            [
                'some response'
            ]
        );

        $expected = $initialRecording;

        $this->jsonObject->storeRecording($initialRecording);

        $actual = [];
        foreach ($this->jsonObject as $recording) {
            $actual[] = $recording;
        }

        $this->assertEquals($expected, $actual[0], 'Storing and reading a recording failed.');
    }

    public function testValidJson(): void
    {
        $request = ['some request'];
        $response = ['some response'];
        $recording = Recording::fromRequestAndResponseArray($request, $response);

        $this->jsonObject->storeRecording($recording);
        $this->jsonObject->storeRecording($recording);

        $this->assertJson(file_get_contents($this->filePath));
    }

    public function testStoreRecordingWhenBlankFileAlreadyExists(): void
    {
        vfsStream::create(['blank_file_test' => '']);
        $filePath = vfsStream::url('test/') . 'blank_file_test';

        $jsonObject = new Json(vfsStream::url('test/'), 'blank_file_test');
        $request = ['some request'];
        $response = ['some response'];
        $recording = Recording::fromRequestAndResponseArray($request, $response);

        $jsonObject->storeRecording($recording);

        $this->assertJson(file_get_contents($filePath));
    }

    private function iterateAndTest(string $json, array $expected, string $message): void
    {
        file_put_contents($this->filePath, $json);

        $actual = [];
        foreach ($this->jsonObject as $object) {
            $actual[] = $object;
        }

        $this->assertEquals($expected, $actual, $message);
    }
}
