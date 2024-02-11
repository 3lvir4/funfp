<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\Iter;
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
class ZipKeyValuesIter implements Iterator, IterOps
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    /**
     * @param Iterator<TKey> $keys
     * @param Iterator<TVal> $values
     */
    public function __construct(
        private readonly Iterator $keys,
        private readonly Iterator $values
    ) {}

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): mixed
    {
        return $this->values->current();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        $this->values->next();
        $this->keys->next();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function key(): mixed
    {
        return $this->keys->current();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function valid(): bool
    {
        return $this->keys->valid() && $this->values->valid();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
        $this->keys->rewind();
        $this->values->rewind();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function getIter(): Iterator
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function values(): IterOps
    {
        return new Iter($this->values);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function keys(): IterOps
    {
        return new Iter($this->keys);
    }
}