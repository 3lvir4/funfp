<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterTrait;
use EmptyIterator;
use Iterator;

/**
 * @template TKey
 * @template TVal
 */
class EmptyIter extends EmptyIterator implements \Countable
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    /**
     * @inheritDoc
     */
    protected function getIter(): Iterator
    {
        return $this;
    }
}