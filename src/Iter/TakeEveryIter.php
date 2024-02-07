<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterTrait;
use Iterator;

/**
 * @template TKey
 * @template TVal
 * @implements Iterator<TKey, TVal>
 */
class TakeEveryIter implements Iterator, \Countable
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    /**
     * @param Iterator $iterator
     * @param int $step
     */
    public function __construct(
        private readonly Iterator $iterator,
        private readonly int $step
    ) {}

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): mixed
    {
        return $this->iterator->current();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $i = $this->step;
        while ($this->iterator->valid() && --$i >= 0) {
            $this->iterator->next();
        }
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
    }

    /**
     * @inheritDoc
     */
    protected function getIter(): Iterator
    {
        return $this;
    }
}