<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterTrait;
use Iterator;

/**
 * @template TKey
 * @template TVal
 * @template UKey
 * @template UVal
 * @implements Iterator<list{TKey, UKey}, list{TVal, UVal}>
 * @psalm-suppress all
 */
class ZipIter implements Iterator, \Countable
{
    /**
     * @use IterTrait<list{TKey, UKey}, list{TVal, UVal}>
     */
    use IterTrait;

    /**
     * @var Iterator<TKey, TVal>
     */
    private Iterator $first;

    /**
     * @var Iterator<UKey, UVal>
     */
    private Iterator $second;

    /**
     * @param Iterator<TKey, TVal> $first
     * @param Iterator<UKey, UVal> $second
     */
    public function __construct(Iterator $first, Iterator $second)
    {
        $this->first = $first;
        $this->second = $second;
    }


    /**
     * @inheritDoc
     */
    #[\Override] public function current(): array
    {
        return [$this->first->current(), $this->second->current()];
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $this->first->next();
        $this->second->next();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function key(): array
    {
        return [$this->first->key(), $this->second->key()];
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function valid(): bool
    {
        return $this->first->valid() && $this->second->valid();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
        $this->first->rewind();
        $this->second->rewind();
    }

    /**
     * @inheritDoc
     */
    protected function getIter(): Iterator
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function join(string $separator = ""): never
    {
        throw new \RuntimeException("Attempt to call join on iterator of iterators.");
    }
}