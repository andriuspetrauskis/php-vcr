<?php

namespace VCR\Storage;

/**
 * Json based storage for records.
 *
 * This storage can be iterated while keeping the memory consumption to the
 * amount of memory used by the largest record.
 */
class Json extends AbstractStorage
{
    /**
     * @inheritDoc
     */
    public function storeRecording(Recording $recording): void
    {
        fseek($this->handle, -1, SEEK_END);
        if (ftell($this->handle) > 2) {
            fwrite($this->handle, ',');
        }
        if (defined('JSON_PRETTY_PRINT')) {
            $json = json_encode($recording, JSON_PRETTY_PRINT);
        } else {
            $json = json_encode($recording);
        }
        fwrite($this->handle, $json . ']');
        fflush($this->handle);
    }

    /**
     * Parses the next record.
     *
     * @return void
     */
    public function next(): void
    {
        $result = json_decode($this->readNextRecord(), true);
        if (is_array($result)) {
            $this->current = new Recording($result);
        }
        ++$this->position;
    }

    /**
     * Returns the next record in raw format.
     *
     * @return string Next record in raw format.
     */
    protected function readNextRecord(): string
    {
        $depth = 0;
        $isInRecord = false;
        $record = '';

        while (false !== ($char = fgetc($this->handle))) {
            if ($char === '{') {
                ++$depth;
            }
            if ($char === '}') {
                --$depth;
            }

            if (!$isInRecord && $char === '{') {
                $isInRecord = true;
            }

            if ($isInRecord) {
                $record .= $char;
            }

            if ($isInRecord && $char === '}' && $depth === 0) {
                break;
            }
        }

        if ($char === false) {
            $this->isEOF = true;
        }

        return $record;
    }

    /**
     * Resets the storage to the beginning.
     *
     * @return void
     */
    public function rewind(): void
    {
        rewind($this->handle);
        $this->isEOF = false;
        $this->position = 0;
    }

    /**
     * Returns true if the current record is valid.
     *
     * @return boolean True if the current record is valid.
     */
    public function valid(): bool
    {
        if ($this->current === null) {
            $this->next();
        }

        return !$this->isEOF;
    }
}
