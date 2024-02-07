<?php

declare(strict_types=1);

namespace Elvir4\FunFp;

use Elvir4\FunFp\Result\Err;
use Elvir4\FunFp\Result\Ok;
use Generator;
use RuntimeException;
use Throwable;

/**
 * @template T
 * @template E
 * @psalm-inheritors Ok|Err
 * @psalm-yield T
 */
abstract class Result
{
    # region Constructors

    /**
     * @template U
     * @param U $value
     * @return Result<U, never>
     */
    final public static function Ok(mixed $value): Result
    {
        return new Ok($value);
    }

    /**
     * @template F
     * @param F $error
     * @return Result<never, F>
     */
    final public static function Err(mixed $error): Result
    {
        return new Err($error);
    }

    /**
     * @template O
     * @param callable(): O $procedure
     * @return Result<O, Throwable>
     */
    final public static function try(callable $procedure): Result
    {
        try {
            return Result::Ok($procedure());
        } catch (Throwable $error) {
            return Result::Err($error);
        }
    }

    /**
     * @template U
     * @template F
     * @template O
     * @param callable(): Generator<int, Result<U, F>, U, O> $expr
     * @return Result<O, F>
     * @psalm-suppress InvalidArgument
     */
    final public static function with(callable $expr): Result
    {
        $gen = $expr();
        while ($gen->valid()) {
            $curr = $gen->current();
            if ($curr->isErr()) {
                return $curr;
            }
            $gen->send($curr->unwrap());
        }
        return Result::Ok($gen->getReturn());
    }

    # endregion Constructors

    # region Getters

    /**
     * Returns the Ok value. In the Err case, if the error value if throwable, it is thrown.
     * Otherwise, throws RuntimeException.
     * @return T
     * @throws RuntimeException if Err variant is not throwable.
     */
    abstract public function unwrap(): mixed;

    /**
     * @param T $default
     * @return T
     */
    abstract public function unwrapOr(mixed $default): mixed;

    /**
     * Returns the Err value, Throws otherwise.
     * @return E
     * @throws RuntimeException if $this is Ok variant.
     */
    abstract public function unwrapErr(): mixed;

    /**
     * Get the value if Ok. Returns the result of the given callback otherwise.
     * @param callable(): T $fallback
     * @return T
     */
    abstract public function unwrapOrElse(callable $fallback): mixed;

    /**
     * @return Option<T>
     */
    abstract public function get(): Option;

    # endregion Getters

    # region State Checkers

    /**
     * @return bool
     * @psalm-assert-if-true Ok<T> $this
     */
    abstract public function isOk(): bool;

    /**
     * @param callable(T): bool $predicate
     * @return bool
     * @psalm-assert-if-true Ok<T> $this
     */
    abstract public function isOkAnd(callable $predicate): bool;

    /**
     * @return bool
     * @psalm-assert-if-true Err<E> $this
     */
    abstract public function isErr(): bool;

    /**
     * @param callable(E): bool $predicate
     * @return bool
     * @psalm-assert-if-true Err<E> $this
     */
    abstract public function isErrAnd(callable $predicate): bool;

    # endregion State Checkers

    # region Maps

    /**
     * @template U
     * @param callable(T): U $f
     * @return Result<U, E>
     */
    abstract public function map(callable $f): Result;

    /**
     * @template F
     * @param callable(E): F $f
     * @return Result<T, F>
     */
    abstract public function mapErr(callable $f): Result;

    /**
     * @template U
     * @param callable(T): Result<U, E> $f
     * @return Result<U, E>
     */
    abstract public function flatMap(callable $f): Result;

    /**
     * @template U
     * @param U $default
     * @param callable(T): U $f
     * @return U
     */
    abstract public function mapOr(mixed $default, callable $f): mixed;

    # endregion Maps
}