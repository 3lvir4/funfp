<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use EmptyIterator;
use Iterator;

/**
 * @template TKey
 * @template TVal
 * @implements IterOps<TKey, TVal>
 * @internal
 */
class EmptyIter extends EmptyIterator implements \Countable, IterOps
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    /**
     * @inheritDoc
     */
    public function getIter(): Iterator
    {
        return $this;
    }
}