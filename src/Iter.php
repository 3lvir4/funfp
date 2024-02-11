<?php

declare(strict_types=1);

namespace Elvir4\FunFp;

use ArrayIterator;
use Elvir4\FunFp\Iter\GenerateIter;
use Elvir4\FunFp\Iter\RepeatIter;
use Elvir4\FunFp\Iter\RewindableIter;
use Exception;
use Generator;
use Iterator;
use IteratorAggregate;
use Throwable;

/**
 * Wrapper around an existing Iterator implementing IterOps thus
 * allowing chaining iterator operations.
 *
 * @template TKey
 * @template-covariant TVal
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
     * @template UKey
     * @template UVal
     * @param iterable<UKey, UVal> $iterable
     * @return IterOps<UKey, UVal>
     * @throws Exception on failure.
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

        if ($iterable instanceof Generator)
            return new RewindableIter($iterable);

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