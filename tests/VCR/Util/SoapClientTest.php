<?php

namespace VCR\Util;

use lapistano\ProxyObject\ProxyBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use VCR\LibraryHooks\SoapHook;
use VCR\VCRException;
use VCR\Util\SoapClient;

class SoapClientTest extends TestCase
{
    private const WSDL = 'https://raw.githubusercontent.com/php-vcr/php-vcr/master/tests/fixtures/soap/wsdl/weather.wsdl';
    private const ACTION = 'http://ws.cdyne.com/WeatherWS/GetCityWeatherByZIP';

    /**
     * @param bool $enabled
     *
     * @return MockObject&SoapHook
     */
    protected function getLibraryHookMock(bool $enabled): MockObject
    {
        $hookMock = $this->getMockBuilder(SoapHook::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEnabled', 'doRequest'])
            ->getMock();

        $hookMock
            ->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue($enabled));

        return $hookMock;
    }

    public function testDoRequest(): void
    {
        $expected = 'Knorx ist groß';

        $hook = $this->getLibraryHookMock(true);
        $hook
            ->expects($this->once())
            ->method('doRequest')
            ->with(
                $this->isType('string'),
                $this->isType('string'),
                $this->isType('string'),
                $this->isType('integer')
            )
            ->will($this->returnValue($expected));

        $client = new SoapClient(self::WSDL);
        $client->setLibraryHook($hook);

        $this->assertEquals(
            $expected,
            $client->__doRequest('Knorx ist groß', self::WSDL, self::ACTION, SOAP_1_2)
        );
    }

    public function testDoRequestOneWayEnabled(): void
    {
        $hook = $this->getLibraryHookMock(true);
        $hook->expects($this->once())->method('doRequest')->will($this->returnValue('some value'));

        $client = new SoapClient(self::WSDL);
        $client->setLibraryHook($hook);

        $this->assertNull($client->__doRequest('Knorx ist groß', self::WSDL, self::ACTION, SOAP_1_2, 1));
    }

    public function testDoRequestOneWayDisabled(): void
    {
        $expected = 'some value';
        $hook = $this->getLibraryHookMock(true);
        $hook ->expects($this->once()) ->method('doRequest')->will($this->returnValue($expected));

        $client = new SoapClient(self::WSDL);
        $client->setLibraryHook($hook);

        $this->assertEquals(
            $expected,
            $client->__doRequest('Knorx ist groß', self::WSDL, self::ACTION, SOAP_1_2, 0)
        );
    }

    public function testDoRequestHandlesHookDisabled(): void
    {
        $client = $this->getMockBuilder(SoapClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['realDoRequest'])
            ->getMock();

        $client
            ->expects($this->once())
            ->method('realDoRequest')
            ->with(
                $this->equalTo('Knorx ist groß'),
                $this->equalTo(self::WSDL),
                $this->equalTo(self::ACTION),
                $this->equalTo(SOAP_1_2),
                $this->equalTo(0)
            );

        $hook = $this->getLibraryHookMock(false);
        $client->setLibraryHook($hook);

        $client->__doRequest('Knorx ist groß', self::WSDL, self::ACTION, SOAP_1_2);
    }

    public function testDoRequestExpectingException(): void
    {
        $exception = '\LogicException';

        $hook = $this->getLibraryHookMock(true);
        $hook
            ->expects($this->once())
            ->method('doRequest')
            ->will(
                $this->throwException(
                    new \LogicException('hook not enabled.')
                )
            );

        $client = new SoapClient(self::WSDL);
        $client->setLibraryHook($hook);

        $this->expectException($exception);

        $client->__doRequest('Knorx ist groß', self::WSDL, self::ACTION, SOAP_1_2);
    }

    public function testLibraryHook(): void
    {
        $client = new class(self::WSDL) extends SoapClient {
            // A proxy to access the protected getLibraryHook method.
            public function publicGetLibraryHook(): SoapHook
            {
                return $this->getLibraryHook();
            }
        };

        $this->assertInstanceOf(SoapHook::class, $client->publicGetLibraryHook());

        $client->setLibraryHook($this->getLibraryHookMock(true));

        $this->assertInstanceOf(SoapHook::class, $client->publicGetLibraryHook());
    }

    public function testGetLastWhateverBeforeRequest(): void
    {
        $client = new SoapClient(self::WSDL);

        $this->assertNull($client->__getLastRequest());
        $this->assertNull($client->__getLastResponse());
    }

    public function testGetLastWhateverAfterRequest(): void
    {
        $request  = 'Knorx ist groß';
        $response = 'some value';

        $hook = $this->getLibraryHookMock(true);
        $hook->expects($this->once())->method('doRequest')->will($this->returnValue($response));

        $client = new SoapClient(self::WSDL);
        $client->setLibraryHook($hook);

        $client->__doRequest($request, self::WSDL, self::ACTION, SOAP_1_2, 0);

        $this->assertEquals($request, $client->__getLastRequest());
        $this->assertEquals($response, $client->__getLastResponse());
    }
}
