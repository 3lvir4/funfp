<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Closure;
use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Iterator;
use IteratorIterator;

/**
 * @template TKey
 * @template TVal
 * @extends IteratorIterator<TKey, TVal, Iterator<TKey, TVal>>
 * @implements IterOps<TKey, TVal>
 * @internal
 */
class SortedIter extends IteratorIterator implements IterOps
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait;

    /**
     * @var Closure(TVal, TVal): int
     */
    private Closure $comparator;

    private bool $preserveKeys;

    /**
     * @param Iterator<TKey, TVal> $iterator
     * @param callable(TVal, TVal): int $comparator
     * @param bool $preserveKeys
     */
    public function __construct(Iterator $iterator, callable $comparator, bool $preserveKeys = true)
    {
        parent::__construct($iterator);
        $this->comparator = $comparator(...);
        $this->preserveKeys = $preserveKeys;
    }


    /**
     * @inheritDoc
     * @psalm-suppress InvalidReturnStatement, InvalidReturnType, MixedArgumentTypeCoercion
     */
    public function getIter(): Iterator
    {
        if ($this->preserveKeys) {
            return new \ArrayIterator($this->toSortedArray($this->comparator));
        } else {
            return new \ArrayIterator($this->toSortedList($this->comparator));
        }
    }

    /**
     * @inheritDoc
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function toList(): array
    {
        return iterator_to_array($this, false);
    }

    /**
     * @inheritDoc
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function toArray(): array
    {
        return iterator_to_array($this, true);
    }
}