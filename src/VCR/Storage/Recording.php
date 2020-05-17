<?php

namespace VCR\Storage;

/**
 * @implements \ArrayAccess<mixed,mixed>
 */
class Recording implements \JsonSerializable, \ArrayAccess
{
    /**
     * @var array<string,mixed>
     */
    private $data;

    /**
     * @param array<string,mixed> $data
     */
    final public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param array<mixed> $request
     * @param array<mixed> $response
     *
     * @return static
     */
    public static function fromRequestAndResponseArray(array $request, array $response): self
    {
        return new static([
            'request' => $request,
            'response' => $response,
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array<mixed>
     */
    public function getRequest(): array
    {
        return $this->data['request'] ?? [];
    }

    /**
     * @return array<mixed>
     */
    public function getResponse(): array
    {
        return $this->data['response'] ?? [];
    }

    /**
     * @inheritDoc
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->getData();
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return $this->getData();
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}
