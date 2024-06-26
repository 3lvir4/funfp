<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Iterator;

/**
 * @template TKey
 * @template TVal
 * @implements Iterator<int, array<TVal>>
 * @implements IterOps<int, array<TVal>>
 * @psalm-suppress all
 */
class ChunkEveryIter implements Iterator, IterOps
{
    /**
     * @use IterTrait<int, array<TVal>>
     */
    use IterTrait;

    private int $index = -1;

    /**
     * @var array<TVal>
     */
    private array $currentChunk = [];

    private bool $isLastItem = false;

    private int $nbElements = 0;

    /**
     * @param Iterator<TKey, TVal> $iterator
     * @param int $count
     * @param int $step
     * @param bool $discard
     * @param iterable<TKey, TVal> $leftover
     * @param bool $preserveKeys
     */
    public function __construct(
        private Iterator $iterator,
        private int $count,
        private int $step,
        private bool $discard,
        private iterable $leftover = [],
        private bool $preserveKeys = false
    ) {
        $this->next();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): array
    {
        if (count($this->currentChunk) !== $this->count && !$this->discard) {
            $count = count($this->currentChunk);
            foreach ($this->leftover as $key => $value) {
                if ($count === $this->count) break;
                $this->preserveKeys
                    ? $this->currentChunk[$key] = $value
                    : $this->currentChunk[] = $value;
                $count++;
            }
        }

        return $this->currentChunk;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $this->currentChunk = array_slice($this->currentChunk, -1, $this->count - $this->step);
        $count = count($this->currentChunk);

        while ($this->iterator->valid() && $count !== $this->count) {
            $curr = $this->iterator->current();
            $key = $this->iterator->key();
            $count++;
            $this->preserveKeys
                ? $this->currentChunk[$key] = $curr
                : $this->currentChunk[] = $curr;

            $this->iterator->next();
            $this->nbElements++;
        }

        $this->isLastItem = !$this->isLastItem && !$this->iterator->valid();
        $this->index++;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function key(): int
    {
        return $this->index;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function valid(): bool
    {
        if (!$this->iterator->valid() && !$this->discard && !$this->isLastItem && $this->index * $this->step < $this->nbElements) {
            $this->isLastItem = true;
            return true;
        }
        if ($this->isLastItem) {
            return !$this->discard;
        }
        return $this->iterator->valid();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
        $this->currentChunk = [];
        $this->index = -1;
        $this->iterator->rewind();
        $this->nbElements = 0;
        $this->next();
    }

    /**
     * @inheritDoc
     */
    public function getIter(): Iterator
    {
        return $this;
    }
}