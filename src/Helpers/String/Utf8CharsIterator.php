<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Helpers\String;

use Iterator;
use Stringable;

/**
 * @internal
 */
class Utf8CharsIterator extends Utf8StringIterator
{
    /**
     * @inheritDoc
     */
    #[\Override] public function current(): string
    {
        $this->calcByteCount();
        if ($this->byteCount === 1)
            return $this->str[$this->ptr];
        else
            return substr($this->str, $this->ptr, $this->byteCount);
    }
}