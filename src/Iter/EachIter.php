<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Closure;
use Elvir4\FunFp\IterTrait;
use Iterator;

/**
 * @template TKey
 * @template TVal
 * @implements Iterator<TKey, TVal>
 */
class EachIter implements Iterator, \Countable
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    /**
     * @var Iterator<TKey, TVal> $iterator
     */
    private readonly Iterator $iterator;

    /**
     * @var Closure(TVal, TKey, Iterator<TKey, TVal>): void $fun
     */
    private Closure $fun;

    /**
     * @param Iterator<TKey, TVal> $iterator
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): void $fun
     */
    public function __construct(Iterator $iterator, callable $fun)
    {
        $this->iterator = $iterator;
        $this->fun = $fun(...);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): mixed
    {
        $curr = $this->iterator->current();
        call_user_func($this->fun, $curr, $this->iterator->key(), $this->iterator);
        return $curr;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $this->iterator->next();
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