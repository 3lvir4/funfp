<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Iterator;
use IteratorIterator;
use Traversable;

/**
 * @template TKey
 * @template TVal
 * @extends IteratorIterator<TKey, TVal, Iterator<TKey, TVal>>
 * @implements IterOps<TKey, TVal>
 */
class InterleaveIter extends IteratorIterator implements IterOps
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    /**
     * @var Iterator<TKey, TVal>
     */
    protected Iterator $interIter;

    private int $index = 0;

    /**
     * @param Iterator<TKey, TVal> $iterator
     * @param Iterator<TKey, TVal> $interIter
     */
    public function __construct(Iterator $iterator, Iterator $interIter)
    {
        parent::__construct($iterator);
        $this->interIter = $interIter;
    }

    public function current(): mixed
    {
        return $this->interIter->valid() && $this->index % 2 !== 0
            ? $this->interIter->current()
            : (parent::valid()
                ? parent::current()
                : $this->interIter->current()
            );
    }

    public function key(): mixed
    {
        return $this->interIter->valid() && $this->index % 2 !== 0
            ? $this->interIter->key()
            : (parent::valid()
                ? parent::key()
                : $this->interIter->key()
            );
    }

    public function next(): void
    {
        ++$this->index % 2 === 0 && $this->interIter->valid()
            ? $this->interIter->next()
            : (parent::valid() ? parent::next() : $this->interIter->next());
    }

    public function valid(): bool
    {
        return parent::valid() || $this->interIter->valid();
    }

    public function rewind(): void
    {
        parent::rewind();
        $this->interIter->rewind();
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