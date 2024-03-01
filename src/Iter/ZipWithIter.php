<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Closure;
use Elvir4\FunFp\Iter;
use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Exception;
use Iterator;

/**
 * @template UVal
 * @implements Iterator<int, UVal>
 * @implements IterOps<int, UVal>
 * @psalm-suppress all
 */
class ZipWithIter implements Iterator, IterOps
{
    /**
     * @use IterTrait<int, UVal>
     */
    use IterTrait;

    /**
     * @var array<int, Iterator>
     */
    private array $iterators;

    /**
     * @var Closure(array): UVal
     */
    private Closure $fun;

    private int $index = 0;

    /**
     * @param Iterator<iterable> $iterator
     * @param callable(array): UVal $fun
     */
    public function __construct(Iterator $iterator, callable $fun)
    {
        $this->fun = $fun(...);
        $this->iterators = [];
        try {
            foreach ($iterator as $innerIterable) {
                $this->iterators[] = (Iter::fromIterable($innerIterable))->getIter();
            }
        } catch (\Exception $e) {
            throw new \RuntimeException("Call of `IterOps::zipWith` on iterator over non-iterables.");
        }
    }


    /**
     * @inheritDoc
     */
    #[\Override] public function current(): mixed
    {
        $zip = [];
        foreach ($this->iterators as $iter) {
            $zip[] = $iter->current();
        }
        return call_user_func($this->fun, $zip);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        array_walk($this->iterators, fn(Iterator &$iter) => $iter->next());
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
        foreach ($this->iterators as $iter) {
            if (!$iter->valid()) return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
        array_walk($this->iterators, fn(Iterator &$iter) => $iter->rewind());
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