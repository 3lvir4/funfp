<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use InfiniteIterator;
use Iterator;

/**
 * @template TKey
 * @template TVal
 * @extends InfiniteIterator<TKey, TVal, Iterator<TKey, TVal>>
 * @implements IterOps<TKey, TVal>
 * @internal
 */
class CycleIter extends InfiniteIterator implements IterOps
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    /**
     * @param Iterator<TKey, TVal> $iterator
     */
    public function __construct(Iterator $iterator)
    {
        parent::__construct($iterator);
    }

    /**
     * @inheritDoc
     */
    public function getIter(): Iterator
    {
        return $this;
    }
}