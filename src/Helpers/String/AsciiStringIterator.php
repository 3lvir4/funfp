<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Helpers\String;

use Iterator;
use Stringable;

/**
 * @implements Iterator<int, string>
 * @internal
 */
class AsciiStringIterator implements Iterator, Stringable
{
    private string $str;
    private int $ptr = 0;
    public function __construct(string $str)
    {
        $this->str = $str;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): string
    {
        return $this->str[$this->ptr];
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $this->ptr++;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function key(): int
    {
        return $this->ptr;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function valid(): bool
    {
        return isset($this->str[$this->ptr]);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
        $this->ptr = 0;
    }

    public function __toString(): string
    {
        return $this->str;
    }
}