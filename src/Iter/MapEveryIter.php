<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Iterator;

/**
 * @template TKey
 * @template TVal
 * @template UVal
 * @extends MapIter<TKey, TVal, UVal>
 */
class MapEveryIter extends MapIter
{
    private int $step;

    private int $index = 0;

    /**
     * @param Iterator<TKey, TVal> $iterable
     * @param int $step
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): UVal $fun
     */
    public function __construct(Iterator $iterable, int $step, callable $fun)
    {
        parent::__construct($iterable, $fun);
        $this->step = $step;
    }

    public function current(): mixed
    {
        return !!$this->step && $this->index % $this->step === 0
            ? parent::current()
            : $this->iterable->current();
    }

    public function next(): void
    {
        parent::next();
        ++$this->index;
    }

    public function rewind(): void
    {
        parent::rewind();
        $this->index = 0;
    }
}