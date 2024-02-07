<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Closure;
use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\IterTrait;
use Iterator;

/**
 * @template TVal
 * @implements Iterator<int, TVal>
 * @implements IterOps<int, TVal>
 * @psalm-suppress all
 * @internal
 */
class RepeatIter implements Iterator, IterOps
{
    /**
     * @use IterTrait<int, TVal>
     */
    use IterTrait;

    /**
     * @var Closure(): TVal
     */
    private Closure $f;

    private int $index = 0;

    /**
     * @param callable(): TVal $f
     */
    public function __construct(callable $f)
    {
        $this->f = $f(...);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): mixed
    {
        return call_user_func($this->f);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        ++$this->index;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function key(): mixed
    {
        return $this->index;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function valid(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
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