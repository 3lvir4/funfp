<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use CallbackFilterIterator;
use Countable;
use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Iterator;

/**
 * @template TKey
 * @template TVal
 * @extends CallbackFilterIterator<TKey, TVal, Iterator<TKey, TVal>>
 * @implements IterOps<int, TKey>
 * @psalm-suppress all
 * @internal
 */
class PositionsIter extends CallbackFilterIterator implements Countable, IterOps
{
    /**
     * @use IterTrait<int, TKey>
     */
    use IterTrait;

    private int $index = 0;

    /**
     * @param Iterator<TKey, TVal> $iterable
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     */
    public function __construct(Iterator $iterable, callable $predicate)
    {
        parent::__construct($iterable, $predicate);
    }

    public function current(): mixed
    {
        return parent::key();
    }

    public function key(): int
    {
        return $this->index;
    }

    public function next(): void
    {
        parent::next();
        $this->index++;
    }

    public function rewind(): void
    {
        parent::rewind();
        $this->index = 0;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function getIter(): Iterator
    {
        return $this;
    }
}