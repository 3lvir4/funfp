<?php

declare(strict_types=1);

namespace Elvir4\FunFp;

use ArrayAccess;
use Countable;
use Elvir4\FunFp\Contracts\ProvidesIterOps;
use Elvir4\FunFp\Iter\RewindableIter;
use IteratorAggregate;
use JsonSerializable;
use Stringable;
use Traversable;

/**
 * Immutable Pair object. Can be accessed and destructured like an array.
 *
 * @template-covariant T0
 * @template-covariant T1
 * @implements ArrayAccess<int<0, 1>, T0|T1>
 * @implements IteratorAggregate<int<0, 1>, T0|T1>
 * @implements ProvidesIterOps<int<0, 1>, T0|T1>
 */
class Pair implements ArrayAccess, Countable, IteratorAggregate, Stringable, JsonSerializable, ProvidesIterOps
{
    /**
     * @param T0 $_0
     * @param T1 $_1
     */
    final public function __construct(
        protected readonly mixed $_0,
        protected readonly mixed $_1
    ) {}

    /**
     * @template U0
     * @param callable(T0): U0 $f
     * @return Pair<U0, T1>
     */
    public function mapFirst(callable $f): Pair
    {
        return new Pair($f($this->_0), $this->_1);
    }

    /**
     * @template U1
     * @param callable(T1): U1 $f
     * @return Pair<T0, U1>
     */
    public function mapLast(callable $f): Pair
    {
        return new Pair($this->_0, $f($this->_1));
    }

    /**
     * @template O
     * @param callable(T0, T1): O $f
     * @return O
     */
    public function fold(callable $f): mixed
    {
        return $f($this->_0, $this->_1);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function offsetExists(mixed $offset): bool
    {
        return $offset === 0 || $offset === 1;
    }

    /**
     * @inheritDoc
     * @psalm-suppress InvalidCast, ImplementedReturnTypeMismatch
     * @psalm-return ($offset is int<0, 0> ? T0 : ($offset is int<1, 1> ? T1 : never))
     */
    #[\Override] public function offsetGet(mixed $offset): mixed
    {
        if ($offset === 0) return $this->_0;
        if ($offset === 1) return $this->_1;
        throw new \OutOfBoundsException("$offset is not a valid index for a Pair.");
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \RuntimeException("Pairs are not mutable.");
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function offsetUnset(mixed $offset): void
    {
        throw new \RuntimeException("Pair members are not allowed to be unset.");
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function count(): int
    {
        return 2;
    }

    /**
     * @inheritDoc
     * @psalm-suppress MoreSpecificReturnType
     */
    #[\Override] public function getIterator(): Traversable
    {
        return $this->iter();
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return "($this->_0,$this->_1)";
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function jsonSerialize(): array
    {
        return [$this->_0, $this->_1];
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function iter(): Traversable&IterOps
    {
        return new RewindableIter((function () {
            yield $this->_0;
            yield $this->_1;
        })());
    }
}