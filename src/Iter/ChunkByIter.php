<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Closure;
use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Iterator;
use function Amp\call;

/**
 * @template TKey
 * @template TVal
 * @implements Iterator<int, array<TKey, TVal>>
 * @implements IterOps<int, array<TKey, TVal>>
 * @psalm-suppress all
 * @internal
 */
class ChunkByIter implements Iterator, IterOps
{
    /**
     * @use IterTrait<int, array<TKey, TVal>>
     */
    use IterTrait;

    /**
     * @var mixed
     */
    private mixed $lastValue = null;

    /**
     * @var TVal[]
     */
    private array $currentChunk = [];

    /**
     * @var Closure(TVal, TKey, Iterator<TKey, TVal>): mixed
     */
    private Closure $fun;

    private bool $strictCompare = true;

    private int $index = 0;

    private bool $preserveKeys = false;

    /**
     * @param Iterator<TKey, TVal> $iterator
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): mixed $fun
     */
    public function __construct(
        private readonly Iterator $iterator,
        callable $fun,
        bool $preserveKeys = false
    ) {
        $this->fun = $fun(...);
        $this->preserveKeys = $preserveKeys;
        if ($this->iterator->valid()) {
            $this->lastValue = call_user_func(
                $fun, $this->iterator->current(), $this->iterator->key(), $this->iterator
            );
            $this->strictCompare = !is_object($this->lastValue);
        }
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): array
    {
        while ($this->iterator->valid()) {
            $curr = $this->iterator->current();
            $key = $this->iterator->key();
            $value = call_user_func($this->fun, $curr, $key, $this->iterator);

            if (!$this->equalsLastValue($value)) {
                $this->lastValue = $value;
                $chunk = $this->currentChunk;

                $this->preserveKeys
                    ? $this->currentChunk = [$key => $curr]
                    : $this->currentChunk = [$curr];

                return $chunk;
            }

            $this->preserveKeys
                ? $this->currentChunk[$key] = $curr
                : $this->currentChunk[] = $curr;

            $this->iterator->next();
        }

        return $this->currentChunk;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $this->iterator->next();
        ++$this->index;
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
        return $this->iterator->valid();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
        $this->iterator->rewind();
        if ($this->iterator->valid()) {
            $this->currentChunk = [];
            $this->lastValue = call_user_func(
                $this->fun, $this->iterator->current(), $this->iterator->key(), $this->iterator
            );
        }
    }

    private function equalsLastValue(mixed $value): bool
    {

        return $this->strictCompare
            ? $value === $this->lastValue
            : $value == $this->lastValue;
    }

    /**
     * @inheritDoc
     */
    public function getIter(): Iterator
    {
        return $this;
    }
}