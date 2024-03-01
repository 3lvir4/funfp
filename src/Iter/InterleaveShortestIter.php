<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

/**
 * @template TKey
 * @template TVal
 * @extends InterleaveIter<TKey, TVal>
 */
class InterleaveShortestIter extends InterleaveIter
{
    private bool $isLastItem = false;

    public function valid(): bool
    {
        $this->isLastItem = !$this->isLastItem && !($this->getInnerIterator()->valid() && $this->interIter->valid());
        return ($this->getInnerIterator()->valid() && $this->interIter->valid()) || $this->isLastItem;
    }

    public function rewind(): void
    {
        parent::rewind();
        $this->isLastItem = false;
    }
}