<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Elvir4\FunFp\Pair;
use Iterator;

/**
 * This iterator is primarily made for handling user supplied generators.
 * It permits usage of IterOps api starting from a generator without causing an error on multiple consumption
 * because of the non-rewindable nature of generators.
 *
 * @template TKey
 * @template-covariant TVal
 * @implements Iterator<TKey, TVal>
 * @implements IterOps<TKey, TVal>
 * @internal
 */
class RewindableIter implements Iterator, IterOps
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    /**
     * @var Iterator<TKey, TVal>
     */
    private Iterator $iterator;

    /**
     * @var array<null|Pair<TKey, TVal>>
     */
    private array $cache;
    private int $index = 0;

    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
        $this->cache = [];
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): mixed
    {
        if (isset($this->cache[$this->index]))
            return $this->cache[$this->index][1];
        $val = $this->iterator->current();
        $this->cache[$this->index] = new Pair($this->iterator->key(), $val);
        return $val;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $this->index++;
        if (!isset($this->cache[$this->index])) {
            $this->iterator->next();
            if ($this->iterator->valid()) {
                $this->cache[$this->index] = NULL;
            }
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function key(): mixed
    {
        if (isset($this->cache[$this->index]))
            return$this->cache[$this->index][0];
        return $this->iterator->key();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function valid(): bool
    {
        return $this->iterator->valid() || isset($this->cache[$this->index]);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
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