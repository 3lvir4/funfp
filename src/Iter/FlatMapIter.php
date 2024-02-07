<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Closure;
use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Iterator;
use function Elvir4\FunFp\constructors\iter;

/**
 * @template TKey
 * @template TVal
 * @template UKey
 * @template UVal
 * @implements Iterator<TKey, UVal>
 * @implements IterOps<TKey, UVal>
 * @psalm-suppress MixedArgument
 * @internal
 */
class FlatMapIter implements Iterator, \Countable, IterOps
{
    /**
     * @use IterTrait<TKey, UVal>
     */
    use IterTrait;

    /**
     * @var Iterator<UKey, UVal>
     */
    private Iterator $inner;

    /***
     * @var Iterator<TKey, TVal>
     */
    private Iterator $outer;

    /**
     * @var Closure(TVal, TKey, Iterator<TKey, TVal>): iterable<UKey, UVal>
     */
    private Closure $fun;

    /**
     * @param Iterator<TKey, TVal> $iterator
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): iterable<UKey, UVal> $fun
     */
    public function __construct(Iterator $iterator, callable $fun)
    {
        $this->fun = $fun(...);
        $this->outer = $iterator;
        $this->setInner(
            $iterator->valid()
                ? $fun($iterator->current(), $iterator->key(), $iterator)
                : new \EmptyIterator()
        );
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): mixed
    {
        return $this->inner->current();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $this->inner->next();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function key(): mixed
    {
        return $this->inner->key();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function valid(): bool
    {
        if (!$this->inner->valid()) {
            $this->outer->next();
            if (!$this->outer->valid()) return false;
            $this->setInner(
                call_user_func($this->fun, $this->outer->current(), $this->outer->key(), $this->outer)
            );
        }
        return $this->inner->valid();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
        $this->outer->rewind();
        $this->setInner(
            $this->outer->valid()
                ? call_user_func($this->fun, $this->outer->current(), $this->outer->key(), $this->outer)
                : new \EmptyIterator()
        );
    }

    /**
     * @template I of iterable<UKey, UVal>
     * @param I $value
     * @return void
     */
    private function setInner(mixed $value): void
    {
        if (is_iterable($value)) {
            $this->inner = iter($value)->unwrap()->getIter();
        } else {
            throw new \UnexpectedValueException("Tried to flatten on a non-iterable value.");
        }
    }

    /**
     * @inheritDoc
     */
    public function getIter(): Iterator
    {
        return $this;
    }
}