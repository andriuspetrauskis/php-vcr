<?php

namespace VCR;

use VCR\Util\Assertion;

/**
 * Encapsulates a HTTP response.
 */
class Response
{
    /**
     * @var array
     */
    protected $status = [
        'code' => null,
        'message' => ''
    ];

    /**
     * @var array<string,string>
     */
    protected $headers = [];
    /**
     * @var string|null
     */
    protected $body;
    /**
     * @var array<string,mixed>
     */
    protected $curlInfo = [];

    protected $httpVersion;

    /**
     * @param string|array $status
     * @param array<string,string> $headers
     * @param string|null $body
     * @param array<string,mixed> $curlInfo
     */
    public function __construct($status, array $headers = [], ?string $body = null, array $curlInfo = [])
    {
        $this->setStatus($status);
        $this->headers = $headers;
        $this->body = $body;
        $this->curlInfo = $curlInfo;
    }

    /**
     * Returns an array representation of this Response.
     *
     * @return array<string,mixed> Array representation of this Request.
     */
    public function toArray(): array
    {
        $body = $this->getBody();
        // Base64 encode when binary
        if (strpos($this->getContentType(), 'application/x-gzip') !== false
            || $this->getHeader('Content-Transfer-Encoding') == 'binary'
        ) {
            $body = base64_encode($body);
        }

        return array_filter(
            [
                'status'    => $this->status,
                'headers'   => $this->getHeaders(),
                'body'      => $body,
                'curl_info' => $this->curlInfo,
            ]
        );
    }

    /**
     * Creates a new Response from a specified array.
     *
     * @param  array<string,mixed>  $response Array representation of a Response.
     * @return Response A new Response from a specified array
     */
    public static function fromArray(array $response): Response
    {
        $body = $response['body'] ?? null;

        $gzip = strpos($response['headers']['Content-Type'] ?? '', 'application/x-gzip') !== false;

        $binary = ($response['headers']['Content-Transfer-Encoding'] ?? '') === 'binary';

        // Base64 decode when binary
        if ($gzip || $binary) {
            $body = base64_decode($response['body']);
        }

        return new static(
            $response['status'] ?? 200,
            $response['headers'] ?? [],
            $body,
            $response['curl_info'] ?? []
        );
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body ?? '';
    }

    /**
     * @return array<string,mixed>|mixed|null
     */
    public function getCurlInfo(?string $option = null)
    {
        if (empty($option)) {
            return $this->curlInfo;
        }

        return $this->curlInfo[$option] ?? null;
    }

    /**
     * @return array<string,string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getStatusCode(): string
    {
        return $this->status['code'];
    }

    public function getContentType(): ?string
    {
        return $this->getHeader('Content-Type');
    }

    public function getHeader(string $key): ?string
    {
        return $this->headers[$key] ?? null;
    }

    /**
     * @return mixed
     */
    public function getHttpVersion()
    {
        return $this->httpVersion;
    }

    /**
     * @return string
     */
    public function getStatusMessage(): string
    {
        return $this->status['message'];
    }

    /**
     * @param string|array $status
     */
    protected function setStatus($status): void
    {
        if (is_array($status)) {
            $this->status = $status;
            if (!empty($status['http_version'])) {
                $this->httpVersion = $status['http_version'];
            }
        } else {
            Assertion::numeric($status, 'Response status must be either an array or a number.');
            $this->status['code'] = $status;
        }
    }
}
