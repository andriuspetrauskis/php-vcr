<?php

namespace VCR\Event;

use PHPUnit\Framework\TestCase;
use VCR\Request;
use VCR\Cassette;
use VCR\Configuration;
use VCR\Storage;

class BeforePlaybackEventTest extends TestCase
{
    /**
     * @var BeforePlaybackEvent
     */
    private $event;

    protected function setUp(): void
    {
        $this->event = new BeforePlaybackEvent(
            new Request('GET', 'http://example.com'),
            new Cassette('test', new Configuration(), new Storage\Blackhole())
        );
    }

    public function testGetRequest()
    {
        $this->assertInstanceOf(Request::class, $this->event->getRequest());
    }

    public function testGetCassette()
    {
        $this->assertInstanceOf(Cassette::class, $this->event->getCassette());
    }
}
