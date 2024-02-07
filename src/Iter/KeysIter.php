<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Iterator;
use IteratorIterator;
use Override;

/**
 * @template TKey
 * @template TVal
 * @extends IteratorIterator<int, TKey, Iterator<TKey, TVal>>
 * @implements IterOps<int, TKey>
 * @psalm-suppress all
 * @internal
 */
class KeysIter extends IteratorIterator implements IterOps
{
    /**
     * @use IterTrait<int, TKey>
     */
    use IterTrait;

    private int $index = 0;

    #[Override] public function current(): mixed
    {
        return $this->getInnerIterator()->key();
    }

    #[Override] public function key(): int
    {
        return $this->index;
    }

    #[Override] public function next(): void
    {
        parent::next();
        ++$this->index;
    }

    #[Override] public function rewind(): void
    {
        parent::rewind();
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