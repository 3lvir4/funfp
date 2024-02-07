<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Iterator;
use LimitIterator;

/**
 * @template TKey
 * @template TVal
 * @extends LimitIterator<TKey, TVal, Iterator<TKey, TVal>>
 * @implements IterOps<TKey, TVal>
 * @internal
 */
class TakeIter extends LimitIterator implements \Countable, IterOps
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    /**
     * @param Iterator<TKey, TVal> $iterable
     * @param int $takeAmount
     */
    public function __construct(
        Iterator $iterable,
        int $takeAmount
    ) {
        parent::__construct($iterable, 0, $takeAmount);
    }

    /**
     * @inheritDoc
     */
    public function getIter(): Iterator
    {
        return $this;
    }
}