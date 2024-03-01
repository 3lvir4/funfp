<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Iterator;
use IteratorIterator;

/**
 * @template TKey
 * @template TVal
 * @implements IterOps<TKey, TVal>
 * @extends  IteratorIterator<TKey, TVal, Iterator<TKey, TVal>>
 */
class IntersperseIter extends IteratorIterator implements IterOps
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    private int $index = 0;

    /**
     * @param Iterator<TKey, TVal> $iterator
     * @param TVal $sep
     */
    public function __construct(
        Iterator $iterator,
        private readonly mixed $sep
    ) {
        parent::__construct($iterator);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): mixed
    {
        return $this->index % 2 === 0
            ? parent::current()
            : $this->sep;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        ++$this->index % 2 === 0 || parent::next();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function key(): mixed
    {
        return parent::key();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function valid(): bool
    {
        return parent::valid();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
        parent::rewind();
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