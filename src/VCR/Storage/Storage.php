<?php

namespace VCR\Storage;

/**
 * Interface for reading and storing records.
 *
 * A Storage can be iterated using standard loops.
 * New recordings can be stored.
 *
 * @extends \Iterator<Recording>
 */
interface Storage extends \Iterator
{
    public function storeRecording(Recording $recording): void;

    /**
     * Returns true if the file did not exist and had to be created.
     *
     * @return boolean TRUE if created, FALSE if not
     */
    public function isNew(): bool;
}
