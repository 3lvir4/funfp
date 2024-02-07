<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Closure;
use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Iterator;

/**
 * @template TKey
 * @template TVal
 * @implements Iterator<TKey, TVal>
 * @implements IterOps<TKey, TVal>
 * @internal
 */
class TakeWhileIter implements Iterator, \Countable, IterOps
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    /**
     * @var Iterator<TKey, TVal>
     */
    private Iterator $iterator;

    /**
     * @var Closure(TVal, TKey, Iterator<TKey, TVal>): bool
     */
    private Closure $predicate;

    /**
     * @param Iterator<TKey, TVal> $iterator
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     */
    public function __construct(Iterator $iterator, callable $predicate)
    {
        $this->iterator = $iterator;
        $this->predicate = $predicate(...);
    }

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
        return $this->iterator->valid()
            && call_user_func(
                $this->predicate,
                $this->iterator->current(),
                $this->iterator->key(),
                $this->iterator
            );
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
    public function getIter(): Iterator
    {
        return $this;
    }
}