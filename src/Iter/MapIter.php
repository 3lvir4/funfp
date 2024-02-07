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
 * @template UVal
 * @implements Iterator<TKey, UVal>
 * @implements IterOps<TKey, UVal>
 * @internal
 */
class MapIter implements Iterator, \Countable, IterOps
{
    /**
     * @use IterTrait<TKey, UVal>
     */
    use IterTrait;

    /**
     * @var Iterator<TKey, TVal>
     */
    private Iterator $iterable;

    /**
     * @var Closure(TVal, TKey, Iterator<TKey, TVal>): UVal
     */
    private Closure $fun;

    /**
     * @param Iterator<TKey, TVal> $iterable
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): UVal $fun
     */
    public function __construct(Iterator $iterable, callable $fun) {
        $this->iterable = $iterable;
        $this->fun = $fun(...);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): mixed
    {
        return call_user_func(
            $this->fun,
            $this->iterable->current(),
            $this->iterable->key(),
            $this->iterable
        );
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $this->iterable->next();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function key(): mixed
    {
        return $this->iterable->key();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function valid(): bool
    {
        return $this->iterable->valid();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
        $this->iterable->rewind();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function getIter(): Iterator
    {
        return $this;
    }
}