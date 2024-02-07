<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterTrait;
use Iterator;
use LimitIterator;

/**
 * @template TKey
 * @template TVal
 * @extends LimitIterator<TKey, TVal, Iterator<TKey, TVal>>
 */
class SliceIter extends LimitIterator implements \Countable
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    /**
     * @param Iterator<TKey, TVal> $iterator
     * @param int $start
     * @param int $amount
     */
    public function __construct(Iterator $iterator ,int $start, int $amount)
    {
        parent::__construct($iterator, $start, $amount);
    }

    /**
     * @inheritDoc
     */
    protected function getIter(): Iterator
    {
        return $this;
    }
}