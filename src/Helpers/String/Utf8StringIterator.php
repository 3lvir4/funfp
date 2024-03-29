<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Helpers\String;

use Iterator;
use Stringable;

/**
 * @implements Iterator<int, string>
 * @internal
 */
abstract class Utf8StringIterator implements Iterator, Stringable
{
    protected string $str;
    protected int $ptr = 0;
    protected int $byteCount = 0;
    public function __construct(string $str)
    {
        $this->str = $str;
        $this->calcByteCount();
    }

    /**
     * @inheritDoc
     */
    abstract public function current(): string;

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $this->ptr += $this->byteCount;
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
        $this->calcByteCount();
    }

    protected function calcByteCount(): void
    {
        $b = @ord($this->str[$this->ptr]);

        if($b < 128) {
            $this->byteCount = 1;
            return;
        }

        if($b < 224) {
            $this->byteCount = 2;
        } else if ($b < 240){
            $this->byteCount = 3;
        } else {
            $this->byteCount = 4;
        }
    }

    public function __toString(): string
    {
        return $this->str;
    }
}