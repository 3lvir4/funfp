<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Elvir4\FunFp\Pair;
use Iterator;

/**
 * @template TKey
 * @template TVal
 * @implements Iterator<TKey, Pair<int, TVal>>
 * @implements IterOps<TKey, Pair<int, TVal>>
 * @psalm-suppress all
 * @internal
 */
class DedupWithCountIter implements Iterator, IterOps
{
    /**
     * @use IterTrait<TKey, Pair<int, TVal>>
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
    private int $lastSeenCount = 1;
    private bool $strictCompare = true;
    private bool $isLastElement = false;

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
            $this->next();
        }
    }

    /**
     * @inheritDoc
     */
    public function getIter(): Iterator
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): Pair
    {
        $curr = $this->iterator->current();
        $count = $this->lastSeenCount;
        $res = $this->lastSeen;
        $this->lastSeen = $curr;
        $this->lastSeenCount = 1;
        return new Pair($count, $res);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $this->iterator->next();
        while ($this->iterator->valid() && $this->equalsLastSeen($this->iterator->current())) {
            $this->lastSeenCount++;
            $this->iterator->next();
        }
        $this->isLastElement = !$this->isLastElement && !$this->iterator->valid();
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
        return $this->iterator->valid() || $this->isLastElement;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
        $this->iterator->rewind();
        $this->lastSeen = $this->iterator->valid() ? $this->iterator->current() : null;
        $this->lastSeenCount = 1;
        $this->isLastElement = false;
        $this->next();
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