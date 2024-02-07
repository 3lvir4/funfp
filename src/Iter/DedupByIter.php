<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Iterator;
use Closure;

/**
 * @template TKey
 * @template TVal
 * @implements Iterator<TKey, TVal>
 * @implements IterOps<TKey, TVal>
 * @psalm-suppress MixedAssignment
 * @internal
 */
class DedupByIter implements Iterator, IterOps
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
     * @var Closure(TVal): mixed
     */
    private Closure $f;

    /**
     * @param Iterator<TKey, TVal> $iterator
     * @param callable(TVal): mixed $f
     */
    public function __construct(Iterator $iterator, callable $f)
    {
        $this->iterator = $iterator;
        $this->f = $f(...);
        if ($this->iterator->valid()) {
            $curr = $this->iterator->current();
            $this->strictCompare = !is_object($curr);
            $this->lastSeen = $f($curr);
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): mixed
    {
        $curr = $this->iterator->current();
        $this->lastSeen = call_user_func($this->f, $curr);
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
        $this->lastSeen = $this->iterator->valid()
            ? call_user_func($this->f, $this->iterator->current())
            : null;
    }

    /**
     * @inheritDoc
     */
    public function getIter(): Iterator
    {
        return $this;
    }

    /**
     * @param TVal $value
     * @return bool
     */
    private function equalsLastSeen(mixed $value): bool
    {
        $res = call_user_func($this->f, $value);
        if ($this->strictCompare) {
            return $res === $this->lastSeen;
        } else {
            return $res == $this->lastSeen;
        }
    }
}