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
class UniqueIter implements Iterator, \Countable
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    /**
     * @var TVal[]
     */
    private array $visited = [];

    private bool $strictCompare = true;

    /**
     * @param Iterator<TKey, TVal> $iterator
     */
    public function __construct(
        private readonly Iterator $iterator
    ) {
        if ($this->iterator->valid()) {
            $this->strictCompare = !is_object($this->iterator->current());
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): mixed
    {
        $curr = $this->iterator->current();
        $this->visited[] = $curr;
        return $curr;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $this->iterator->next();
        while ($this->iterator->valid() && $this->inVisited($this->iterator->current())) {
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
        $this->visited = [];
    }

    /**
     * @param TVal $value
     * @return bool
     */
    private function inVisited(mixed $value): bool
    {
        return in_array($value, $this->visited, $this->strictCompare);
    }

    /**
     * @inheritDoc
     */
    protected function getIter(): Iterator
    {
        return $this;
    }
}