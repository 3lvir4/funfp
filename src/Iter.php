<?php

declare(strict_types=1);

namespace Elvir4\FunFp;

use ArrayIterator;
use Elvir4\FunFp\Iter\GenerateIter;
use Elvir4\FunFp\Iter\RepeatIter;
use Exception;
use Iterator;
use IteratorAggregate;
use Throwable;

/**
 * @template TKey
 * @template TVal
 * @implements IteratorAggregate<TKey, TVal>
 * @implements IterOps<TKey, TVal>
 */
class Iter implements \Countable, IteratorAggregate, IterOps
{
    /**
     * @use IterTrait<TKey, TVal>
     */
    use IterTrait { getIter as public; }

    /**
     * @param Iterator<TKey, TVal> $iterator
     */
    public function __construct(
        private readonly Iterator $iterator
    ) {}

    /**
     * @template U
     * @param U $value
     * @return IterOps<int, U>
     */
    public static function repeat(mixed $value): IterOps
    {
        return new RepeatIter(static fn() => $value);
    }

    /**
     * Returns an iterator that yields the result of the given callback infinitely.
     * @template T
     * @param callable(): T $f
     * @return IterOps<int, T>
     */
    public static function repeatWith(callable $f): IterOps
    {
        return new RepeatIter($f);
    }

    /**
     * @template T
     * @param T $initialValue
     * @param callable(T): T $genFn
     * @return IterOps<int, T>
     */
    public static function generate($initialValue, callable $genFn): IterOps
    {
        return new GenerateIter($initialValue, $genFn);
    }

    /**
     * @template UKey
     * @template UVal
     * @param iterable<UKey, UVal> $iterable
     * @return IterOps<UKey, UVal>
     * @throws Exception
     * @psalm-suppress InvalidReturnStatement, InvalidReturnType
     */
    public static function fromIterable(iterable $iterable): IterOps
    {
        if (is_array($iterable))
            return new Iter(new ArrayIterator($iterable));

        if ($iterable instanceof IteratorAggregate) {
            while ($iterable instanceof IteratorAggregate) {
                $iterable = $iterable->getIterator();
            }
            /** @var array|Iterator $iterable */
            return Iter::fromIterable($iterable);
        }

        /** @var Iterator $iterable */
        return new Iter($iterable);
    }

    /**
     * @inheritDoc
     * @internal
     */
    #[\Override] public function getIterator(): Iterator
    {
        return $this->iterator;
    }

    /**
     * @inheritDoc
     */
    public function getIter(): Iterator
    {
        return $this->iterator;
    }
}