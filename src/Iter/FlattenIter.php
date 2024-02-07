<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Iterator;
use function Elvir4\FunFp\constructors\iter;

/**
 * @template TKey
 * @template TVal
 * @implements Iterator<TKey, TVal>
 * @implements IterOps<TKey, TVal>
 * @psalm-suppress MixedArgument
 * @internal
 */
class FlattenIter implements Iterator, \Countable, IterOps
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    /**
     * @var Iterator<TKey, TVal>
     */
    private Iterator $inner;

    /***
     * @var Iterator<iterable<TKey, TVal>>
     */
    private Iterator $outer;

    /**
     * @param Iterator<iterable<TKey, TVal>> $iterator
     */
    public function __construct(Iterator $iterator)
    {
        $this->outer = $iterator;
        $this->setInner($iterator->valid() ? $iterator->current() : new \EmptyIterator());
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
            $this->setInner($this->outer->current());
        }
        return $this->inner->valid();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
        $this->outer->rewind();
        $this->setInner($this->outer->valid() ? $this->outer->current() : new \EmptyIterator());
    }

    /**
     * @template I of iterable<TKey, TVal>
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