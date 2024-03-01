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
     * @var null|array<TVal>
     */
    private ?array $items = null;

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
     * @psalm-suppress InvalidReturnStatement, InvalidReturnType
     */
    public function getIter(): Iterator
    {
        if ($this->preserveKeys) {
            $this->items = $this->items ?? iterator_to_array($this->getInnerIterator());
            uasort($this->items, $this->comparator);
            return new \ArrayIterator($this->items);
        } else {
            $this->items = $this->items ?? iterator_to_array($this->getInnerIterator(), false);
            usort($this->items, $this->comparator);
            return new \ArrayIterator($this->items);
        }
    }
}