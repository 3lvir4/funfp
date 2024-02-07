<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterTrait;
use Iterator;
use IteratorIterator;
use Override;

/**
 * @template TKey
 * @template TVal
 * @extends IteratorIterator<int, TVal, Iterator<TKey, TVal>>
 * @psalm-suppress all
 */
class ValuesIter extends IteratorIterator
{
    /**
     * @use IterTrait<int, TVal>
     */
    use IterTrait;

    private int $index = 0;

    #[Override] public function current(): mixed
    {
        return $this->getInnerIterator()->current();
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
    protected function getIter(): Iterator
    {
        return $this;
    }
}