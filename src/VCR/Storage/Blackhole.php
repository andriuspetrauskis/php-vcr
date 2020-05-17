<?php

namespace VCR\Storage;

/**
 * Backhole storage, the storage that looses everything.
 */
class Blackhole implements Storage
{
    public function storeRecording(Recording $recording): void
    {
    }

    public function isNew(): bool
    {
        return true;
    }

    public function current()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function key()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function next(): void
    {
    }

    public function rewind(): void
    {
    }

    public function valid(): bool
    {
        return false;
    }
}
