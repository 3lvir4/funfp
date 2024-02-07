<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Iterator;

/**
 * @template TKey
 * @template TVal
 * @implements Iterator<TKey, list{int, TVal}>
 * @implements IterOps<TKey, list{int, TVal}>
 * @psalm-suppress all
 * @internal
 */
class EnumerateIter implements Iterator, \Countable, IterOps
{
    /**
     * @use IterTrait<TKey, list{int, TVal}>
     */
    use IterTrait;

    private int $index = 0;

    /**
     * @param Iterator<TKey, TVal> $iterator
     */
    public function __construct(private readonly Iterator $iterator) {}

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): array
    {
        return [$this->index, $this->iterator->current()];
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $this->iterator->next();
        ++$this->index;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function key(): mixed
    {
        return $this->iterator->key();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function valid(): bool
    {
        return $this->iterator->valid();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
        $this->iterator->rewind();
        $this->index = 0;
    }

    /**
     * @inheritDoc
     */
    public function getIter(): Iterator
    {
        return $this;
    }
}
