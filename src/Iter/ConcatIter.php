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
class ConcatIter implements Iterator, \Countable, IterOps
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    /**
     * @var Iterator<TKey, TVal>[]
     */
    private array $iterators;

    private int $iterIndex = 0;

    private int $iterCount;

    /**
     * @param Iterator<TKey, TVal> $head
     * @param Iterator<TKey, TVal>[] $tail
     */
    public function __construct(Iterator $head, array $tail)
    {
        $this->iterators = [$head, ...$tail];
        $this->iterCount = count($this->iterators);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): mixed
    {
        return $this->iterators[$this->iterIndex]->current();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $this->iterators[$this->iterIndex]->next();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function key(): mixed
    {
        return $this->iterators[$this->iterIndex]->key();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function valid(): bool
    {
        if ($this->iterIndex !== $this->iterCount - 1) {
            if (!$this->iterators[$this->iterIndex]->valid()) {
                $this->iterIndex++;
                $this->iterators[$this->iterIndex]->rewind();
            }
            return true;
        }
        return $this->iterators[$this->iterIndex]->valid();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
        while ($this->iterIndex !== 0) {
            $this->iterators[$this->iterIndex]->rewind();
            $this->iterIndex--;
        }
        $this->iterators[0]->rewind();
    }

    /**
     * @inheritDoc
     */
    public function getIter(): Iterator
    {
        return $this;
    }
}