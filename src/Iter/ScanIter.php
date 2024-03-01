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
 */
class ScanIter implements Iterator, IterOps
{
    /**
     * @use IterTrait<TKey, UVal>
     */
    use IterTrait;

    /**
     * @var Iterator<TKey, TVal>
     */
    private Iterator $iterator;

    /**
     * @var Closure(UVal, TVal, TKey): UVal
     */
    private Closure $fun;

    /**
     * @var UVal
     */
    private mixed $acc;

    /**
     * @var UVal
     */
    private mixed $initialValue;

    /**
     * @param Iterator<TKey, TVal> $iterator
     * @param UVal $initialValue
     * @param callable(UVal, TVal, TKey): UVal $fun
     */
    public function __construct(Iterator $iterator, mixed $initialValue, callable $fun)
    {
        $this->iterator = $iterator;
        $this->fun = $fun(...);
        $this->acc = $initialValue;
        $this->initialValue = $initialValue;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): mixed
    {
        $this->acc = call_user_func($this->fun, $this->acc, $this->iterator->current(), $this->iterator->key());
        return $this->acc;
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
        $this->acc = $this->initialValue;
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