<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Iterator;

/**
 * @template TKey
 * @template TVal
 * @implements Iterator<TKey, TVal>
 * @implements IterOps<TKey, TVal>
 * @internal
 */
class SkipEveryIter implements Iterator, \Countable, IterOps
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    private int $stepCount;

    /**
     * @param Iterator<TKey, TVal> $iterator
     * @param int $step
     */
    public function __construct(
        private readonly Iterator $iterator,
        private readonly int $step
    ) {
        $this->stepCount = 0;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): mixed
    {
        return $this->iterator->current();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $this->iterator->next();
        $this->updateStepCount();
        if ($this->iterator->valid() && $this->stepCount === $this->step - 1) {
            $this->iterator->next();
            $this->updateStepCount();
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
        if ($this->step !== 0) $this->iterator->next();
    }

    private function updateStepCount(): void
    {
        if ($this->step !== 0) {
            $this->stepCount = ($this->stepCount + 1) % $this->step;
            return;
        }
    }

    /**
     * @inheritDoc
     */
    public function getIter(): Iterator
    {
        return $this;
    }
}