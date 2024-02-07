<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterTrait;
use Iterator;

/**
 * @template TKey
 * @template TVal
 * @implements Iterator<TKey, TVal>
 */
class DedupIter implements Iterator, \Countable
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
     * @var ?TVal $lastSeen
     */
    private mixed $lastSeen = null;
    private bool $strictCompare = true;

    /**
     * @param Iterator<TKey, TVal> $iterator
     */
    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
        if ($this->iterator->valid()) {
            $curr = $this->iterator->current();
            $this->strictCompare = !is_object($curr);
            $this->lastSeen = $curr;
        }
    }

    /**
     * @inheritDoc
     */
    protected function getIter(): Iterator
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): mixed
    {
        $curr = $this->iterator->current();
        $this->lastSeen = $curr;
        return $curr;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $this->iterator->next();
        while ($this->iterator->valid() && $this->equalsLastSeen($this->iterator->current())) {
            $this->iterator->next();
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function key(): mixed
    {
        return $this->iterator->key();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function valid(): bool
    {
        return $this->iterator->valid();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
        $this->iterator->rewind();
        $this->lastSeen = $this->iterator->valid() ? $this->iterator->current() : null;
    }

    /**
     * @param TVal $value
     * @return bool
     */
    private function equalsLastSeen(mixed $value): bool
    {
        if ($this->strictCompare) {
            return $value === $this->lastSeen;
        } else {
            return $value == $this->lastSeen;
        }
    }
}