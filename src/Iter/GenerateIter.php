<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Closure;
use Elvir4\FunFp\IterTrait;
use Iterator;

/**
 * @template TVal
 * @implements Iterator<int, TVal>
 * @psalm-suppress all
 */
class GenerateIter implements Iterator
{
    /**
     * @use IterTrait<int, TVal>
     */
    use IterTrait;

    /**
     * @var Closure(TVal): TVal
     */
    private Closure $genFun;

    /**
     * @var TVal
     */
    private readonly mixed $initialValue;

    /**
     * @var TVal
     */
    private mixed $currentValue;

    private int $index = 0;

    /**
     * @var TVal[]
     */
    private array $cache = [];

    /**
     * @param TVal $initialValue
     * @param callable(TVal): TVal $genFun
     */
    public function __construct(mixed $initialValue, callable $genFun)
    {
        $this->genFun = $genFun(...);
        $this->currentValue = $initialValue;
        $this->initialValue = $initialValue;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function current(): mixed
    {
        return array_key_exists($this->index, $this->cache)
            ? $this->cache[$this->index]
            : $this->currentValue;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function next(): void
    {
        ++$this->index;
        if (array_key_exists($this->index, $this->cache)) {
            $this->currentValue = $this->cache[$this->index];
            return;
        }
        $this->currentValue = $this->cache[$this->index] = call_user_func($this->genFun, $this->currentValue);
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
        return true;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function rewind(): void
    {
        $this->currentValue = $this->initialValue;
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