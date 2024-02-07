<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use CallbackFilterIterator;
use Closure;
use Countable;
use Elvir4\FunFp\IterTrait;
use Iterator;

/**
 * @template TKey
 * @template TVal
 * @extends CallbackFilterIterator<TKey, TVal, Iterator<TKey, TVal>>
 */
class FilterIter extends CallbackFilterIterator implements Countable
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    /**
     * @param Iterator<TKey, TVal> $iterable
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     */
    public function __construct(Iterator $iterable, callable $predicate)
    {
        parent::__construct($iterable, $predicate);
    }

    /**
     * @inheritDoc
     */
    #[\Override] protected function getIter(): Iterator
    {
        return $this;
    }
}