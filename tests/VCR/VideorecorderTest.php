<?php

namespace VCR;

use lapistano\ProxyObject\ProxyBuilder;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use VCR\Configuration;
use VCR\Videorecorder;
use VCR\Util\HttpClient;
use VCR\Cassette;

/**
 * Test Videorecorder.
 */
class VideorecorderTest extends TestCase
{
    public function testCreateVideorecorder(): void
    {
        $this->assertInstanceOf(
            Videorecorder::class,
            new Videorecorder(new Configuration(), new Util\HttpClient(), VCRFactory::getInstance())
        );
    }

    public function testInsertCassetteEjectExisting(): void
    {
        vfsStream::setup('testDir');
        $factory = VCRFactory::getInstance();
        $configuration = $factory->get(Configuration::class);
        $configuration->setCassettePath(vfsStream::url('testDir'));
        $configuration->enableLibraryHooks([]);
        $videorecorder = $this->getMockBuilder(Videorecorder::class)
            ->setConstructorArgs([$configuration, new Util\HttpClient(), VCRFactory::getInstance()])
            ->setMethods(['eject'])
            ->getMock();

        $videorecorder->expects($this->exactly(2))->method('eject');

        $videorecorder->turnOn();
        $videorecorder->insertCassette('cassette1');
        $videorecorder->insertCassette('cassette2');
        $videorecorder->turnOff();
    }

    public function testHandleRequestRecordsRequestWhenModeIsNewRecords(): void
    {
        $request = new Request('GET', 'http://example.com', ['User-Agent' => 'Unit-Test']);
        $response = new Response(200, [], 'example response');
        $client = $this->getClientMock($request, $response);
        $configuration = new Configuration();
        $configuration->enableLibraryHooks([]);
        $configuration->setMode('new_episodes');

        $videorecorder = new class($configuration, $client, VCRFactory::getInstance()) extends Videorecorder {
            public function setCassette(Cassette $cassette): void
            {
                $this->cassette = $cassette;
            }
        };

        $videorecorder->setCassette($this->getCassetteMock($request, $response));

        $this->assertEquals($response, $videorecorder->handleRequest($request));
    }

    public function testHandleRequestThrowsExceptionWhenModeIsNone(): void
    {
        $this->expectException(
            'LogicException',
            "The request does not match a previously recorded request and the 'mode' is set to 'none'. "
            . "If you want to send the request anyway, make sure your 'mode' is set to 'new_episodes'."
        );

        $request = new Request('GET', 'http://example.com', ['User-Agent' => 'Unit-Test']);
        $response = new Response(200, [], 'example response');
        $client = $this->getMockBuilder(HttpClient::class)->getMock();
        $configuration = new Configuration();
        $configuration->enableLibraryHooks([]);
        $configuration->setMode('none');

        $videorecorder = new class($configuration, $client, VCRFactory::getInstance()) extends Videorecorder {
            public function setCassette(Cassette $cassette): void
            {
                $this->cassette = $cassette;
            }
        };

        $videorecorder->setCassette($this->getCassetteMock($request, $response, 'none'));

        $videorecorder->handleRequest($request);
    }

    public function testHandleRequestRecordsRequestWhenModeIsOnceAndCassetteIsNew(): void
    {
        $request = new Request('GET', 'http://example.com', ['User-Agent' => 'Unit-Test']);
        $response = new Response(200, [], 'example response');
        $client = $this->getClientMock($request, $response);
        $configuration = new Configuration();
        $configuration->enableLibraryHooks([]);
        $configuration->setMode('once');

        $videorecorder = new class($configuration, $client, VCRFactory::getInstance()) extends Videorecorder {
            public function setCassette(Cassette $cassette): void
            {
                $this->cassette = $cassette;
            }
        };

        $videorecorder->setCassette($this->getCassetteMock($request, $response, 'once', true));

        $this->assertEquals($response, $videorecorder->handleRequest($request));
    }

    public function testHandleRequestThrowsExceptionWhenModeIsOnceAndCassetteIsOld(): void
    {
        $this->expectException(
            'LogicException',
            "The request does not match a previously recorded request and the 'mode' is set to 'once'. "
            . "If you want to send the request anyway, make sure your 'mode' is set to 'new_episodes'."
        );

        $request = new Request('GET', 'http://example.com', ['User-Agent' => 'Unit-Test']);
        $response = new Response(200, [], 'example response');
        $client = $this->getMockBuilder(HttpClient::class)->getMock();
        $configuration = new Configuration();
        $configuration->enableLibraryHooks([]);
        $configuration->setMode('once');

        $videorecorder = new class($configuration, $client, VCRFactory::getInstance()) extends Videorecorder {
            public function setCassette(Cassette $cassette): void
            {
                $this->cassette = $cassette;
            }
        };

        $videorecorder->setCassette($this->getCassetteMock($request, $response, 'once', false));

        $videorecorder->handleRequest($request);
    }

    /**
     * @param Request $request
     * @param Response $response
     *
     * @return MockObject|HttpClient
     */
    protected function getClientMock(Request $request, Response $response): MockObject
    {
        $client = $this->getMockBuilder(HttpClient::class)->setMethods(['send'])->getMock();
        $client
            ->expects($this->once())
            ->method('send')
            ->with($request)
            ->will($this->returnValue($response));

        return $client;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param string $mode
     * @param bool $isNew
     *
     * @return MockObject&Cassette
     */
    protected function getCassetteMock(
        Request $request,
        Response $response,
        string $mode = VCR::MODE_NEW_EPISODES,
        bool $isNew = false
    ): MockObject {
        $cassette = $this->getMockBuilder(Cassette::class)
            ->disableOriginalConstructor()
            ->setMethods(['record', 'playback', 'isNew', 'getName'])
            ->getMock();
        $cassette
            ->expects($this->once())
            ->method('playback')
            ->with($request)
            ->will($this->returnValue(null));
        $cassette
            ->method('getName')
            ->will($this->returnValue('foobar'));

        if (VCR::MODE_NEW_EPISODES === $mode || VCR::MODE_ONCE === $mode && $isNew === true) {
            $cassette
                ->expects($this->once())
                ->method('record')
                ->with($request, $response);
        }

        if ($mode == 'once') {
            $cassette
                ->expects($this->once())
                ->method('isNew')
                ->will($this->returnValue($isNew));
        }

        return $cassette;
    }
}
