<?php

declare(strict_types=1);

namespace Elvir4\FunFp;

use Elvir4\FunFp\Option\None;
use Elvir4\FunFp\Option\Some;
use Generator;
use Iterator;
use RuntimeException;

/**
 * @template T
 * @psalm-inheritors Some|None
 * @psalm-yield T
 */
abstract class Option
{
    # region Constructors

    /**
     * @template U
     * @param U $value
     * @return Option<U>
     */
    final public static function Some(mixed $value): Option
    {
        return new Some($value);
    }

    /**
     * @return Option<mixed>
     */
    final public static function None(): Option
    {
        return None::getInstance();
    }

    /**
     * Wraps a value into an Option.
     * If the value is equal to null (or to the specified $nullValue), it will return None variant.
     * @template U
     * @param U $value
     * @param mixed $nullValue
     * @return Option<U>
     */
    final public static function wrap(mixed $value, mixed $nullValue = null): Option
    {
        if ($value === $nullValue) return Option::None();
        return Option::Some($value);
    }

    /**
     * Wraps the return value of the given procedure into an Option.
     * If the value is equal to null (or to the specified $nullValue), it will return None variant.
     * @template U
     * @param callable(): U $procedure
     * @param mixed $nullValue
     * @return Option<U>
     */
    final public static function wrapProcedure(callable $procedure, mixed $nullValue = null): Option
    {
        $value = $procedure();
        if ($value === $nullValue) return Option::None();
        return Option::Some($value);
    }

    /**
     * @template U
     * @template O
     * @param callable(): Generator<int, Option<U>, U, O> $expr
     * @return Option<O>
     * @psalm-suppress InvalidArgument
     */
    final public static function with(callable $expr): Option
    {
        $gen = $expr();
        while ($gen->valid()) {
            $curr = $gen->current();
            if ($curr->isNone()) {
                return $curr;
            }
            $gen->send($curr->unwrap());
        }
        return Option::Some($gen->getReturn());
    }

    /**
     * @template U
     * @param callable(): U $procedure
     * @return Option<U>
     */
    final public static function try(callable $procedure): Option
    {
        try {
            return Option::Some($procedure());
        } catch (\Throwable) {
            return Option::None();
        }
    }

    # endregion Constructors

    # region Getters

    /**
     * Get the value out of the Option if `Some` variant.
     * Throws otherwise.
     * @psalm-mutation-free
     * @return T
     * @throws RuntimeException when variant is `None`
     */
    abstract public function unwrap(): mixed;

    /**
     * Get the value out of the Option if `Some` variant.
     * Returns the provided default value otherwise.
     * @psalm-mutation-free
     * @param T $default
     * @return T
     */
    abstract public function unwrapOr(mixed $default): mixed;

    /**
     * Get the value out of the Option if `Some` variant.
     * Returns the result of the provided callable otherwise.
     * @param callable(): T $f
     * @return T
     */
    abstract public function unwrapOrElse(callable $f): mixed;

    /**
     * @return T|null
     */
    abstract public function getNullable(): mixed;

    # endregion Getters

    # region State Checkers

    /**
     * @return bool
     * @psalm-assert-if-true Some<T> $this
     */
    abstract public function isSome(): bool;

    /**
     * @return bool
     * @psalm-assert-if-true None $this
     */
    abstract public function isNone(): bool;

    /**
     * @param callable(T): bool $predicate
     * @return bool
     * @psalm-assert-if-true Some<T> $this
     */
    abstract public function isSomeAnd(callable $predicate): bool;

    # endregion State Checkers

    # region Maps

    /**
     * @template O
     * @param callable(T): O $f
     * @return Option<O>
     */
    abstract public function map(callable $f): Option;

    /**
     * @template O
     * @param callable(T): Option<O> $f
     * @return Option<O>
     */
    abstract public function flatMap(callable $f): Option;

    /**
     * @param callable(T): bool $predicate
     * @return Option<T>
     */
    abstract public function filter(callable $predicate): Option;

    /**
     * @template O
     * @param callable(T): O $f
     * @param O $default
     * @return O
     */
    abstract public function mapOr(mixed $default, callable $f): mixed;

    /**
     * @template U
     * @return Option
     * @psalm-return (T is Option<U> ? Option<U> : Option<T>)
     */
    abstract public function flatten(): Option;

    # endregion Maps
}