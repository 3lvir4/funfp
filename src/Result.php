<?php

declare(strict_types=1);

namespace Elvir4\FunFp;

use Elvir4\FunFp\Result\Err;
use Elvir4\FunFp\Result\Ok;
use Generator;
use RuntimeException;
use Throwable;

/**
 * Represents a result of an operation that may either succeed (Ok) or fail (Err).
 *
 * This class provides a way to encapsulate the result of an operation that can either succeed or fail.
 * Instances of this class can either be in the Ok state, containing a successful result value,
 * or in the Err state, containing an error value indicating the failure.
 *
 * It mimics [existing constructs of other languages](https://en.wikipedia.org/wiki/Result_type).
 *
 * @template-covariant T The type of the value in the Ok variant.
 * @template-covariant E The type of the error value in the Err variant.
 * @psalm-inheritors Ok|Err
 * @psalm-yield T
 * @psalm-suppress InvalidTemplateParam
 */
abstract class Result
{
    # region Constructors

    /**
     * Creates a new Result instance representing a successful outcome with the provided value.
     *
     * Example:
     * ```
     * $result = Result::Ok(42);
     * ```
     *
     * @template U
     * @param U $value The value to wrap in.
     * @return Result<U, never>
     */
    final public static function Ok(mixed $value): Result
    {
        return new Ok($value);
    }

    /**
     * Creates a new Result representing a failure outcome with the provided error.
     *
     * Example:
     * ```
     * $result = Result::Err("Error occurred");
     * ```
     *
     * @template F
     * @param F $error The error to wrap in.
     * @return Result<never, F>
     */
    final public static function Err(mixed $error): Result
    {
        return new Err($error);
    }

    /**
     * Attempts to execute the given procedure and returns the result as Ok if successful.
     * If an exception is thrown during execution, returns the exception object as Err.
     *
     * Example:
     * ```
     * $result = Result::try(fn () => 10 / 2);
     * // $result is Ok(5)
     * $result = Result::try(fn () => 10 / 0);
     * // $result is Err(DivisionByZeroError)
     * ```
     *
     * @template O Output type.
     * @param callable(): O $procedure The procedure to execute.
     * @return Result<O, Throwable> Outcome of the procedure.
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
     * This method allows you to define a sequence of operations using a generator expression.
     * Each yield of the sequence has to produce either an Ok or Err result.
     *
     * If any result in the sequence is Err, the method short-circuits and returns the Err value.
     * If the sequence completes without encountering any Err results, the final Ok value is returned.
     * It acts as syntactic sugar over multiple *flatMap* calls.
     * Similar to [for comprehensions in Scala](https://docs.scala-lang.org/tour/for-comprehensions.html)
     * or
     * [**?** operator in Rust](https://doc.rust-lang.org/reference/expressions/operator-expr.html#the-question-mark-operator).
     *
     * Example 1: Processing a series of operations with success.
     * ```
     *  $result = Result::with(function () {
     *      $a = yield Result::Ok(10);          // Step 1: Ok(10)
     *      $b = yield Result::Ok(20);          // Step 2: Ok(20)
     *      $c = yield Result::Ok(30);          // Step 3: Ok(30)
     *      return $a + $b + $c;                // All were Ok, hence it returns Ok(60).
     *  });
     *  // $result is Ok(60)
     * ```
     *
     * Example 2: Processing a series of operations with an error.
     * ```
     *  $result = Result::with(function () {
     *      $a = yield Result::Ok(10);          // Step 1: Ok(10)
     *      $b = yield Result::Err("Error");    // Step 2: Err("Error")
     *      $c = yield Result::Ok(30);          // This step is skipped due to the error.
     *      return $a + $b + $c;                // It never reaches here.
     *  });
     *  // $result is Err("Error")
     * ```
     *
     * @template U Yielded values type.
     * @template F Yielded errors type.
     * @template O Return success type.
     * @param callable(): Generator<int, Result<U, F>, U, O> $expr Generator expression to run.
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
     * Returns the Ok value. If the result is in the Err state, throws a RuntimeException or the contained error
     * value if it's throwable.
     *
     * Example:
     * ```
     * $result = Result::Ok(42);
     * $value = $result->unwrap(); // $value is 42
     *
     * $result = Result::Err("Bad outcome.");
     * $value = $result->unwrap(); // throws RuntimeException("Called `Result::unwrap()` on Err variant.")
     * ```
     *
     * @return T The Ok value.
     * @throws RuntimeException If the result is in the Err state.
     */
    abstract public function unwrap(): mixed;

    /**
     * Acts like unwrap but returns the provided default value if the result is in the Err state.
     *
     * Example:
     * ```
     * $result = Result::Err("Error occurred");
     * $value = $result->unwrapOr(0); // $value is 0
     * ```
     *
     * @param T $default The default value to return if the result is in the Err state.
     * @return T
     */
    abstract public function unwrapOr(mixed $default): mixed;

    /**
     * Returns the Err value. If the result is in the Ok state, throws a RuntimeException.
     *
     * Example:
     * ```
     * $result = Result::Err("Error occurred");
     * $error = $result->unwrapErr(); // $error is "Error occurred"
     * ```
     *
     * @return E The Err value.
     * @throws RuntimeException If the result is in the Ok state.
     */
    abstract public function unwrapErr(): mixed;

    /**
     * Gets the Ok value if the result is in the Ok state. Otherwise, returns the result of the provided callback.
     *
     * Example:
     * ```
     * $result = Result::Err("Error occurred");
     * $value = $result->unwrapOrElse(fn() => 0); // $value is 0
     * ```
     *
     * @param callable(): T $fallback The callback to execute if the result is in the Err state.
     * @return T
     */
    abstract public function unwrapOrElse(callable $fallback): mixed;

    /**
     * Returns an Option containing the Ok value if the result is in the Ok state.
     * Otherwise, returns None.
     *
     * Example:
     * ```
     * $result = Result::Ok(42);
     * $option = $result->get(); // $option is Some(42)
     *
     * $result = Result::Err("Error occurred");
     * $option = $result->get(); // $option is None
     * ```
     *
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
     * Transforms the Ok value using the provided callback if the result is in the Ok state.
     * If the result is in the Err state, the method returns the result unchanged.
     *
     * Example:
     * ```
     * $result = Result::Ok(42);
     * $newResult = $result->map(fn($value) => $value * 2); // $newResult is Ok(84)
     * ```
     *
     * @template U
     * @param callable(T): U $f The callback to apply to the Ok value.
     * @return Result<U, E> The transformed Ok or the original Err.
     */
    abstract public function map(callable $f): Result;

    /**
     * Transforms the Err value using the provided callback if the result is in the Err state.
     * If the result is in the Ok state, the method returns the result unchanged.
     *
     * Example:
     * ```
     * $result = Result::Err("Error occurred");
     * $newResult = $result->mapErr(fn($error) => strtoupper($error)); // $newResult is Err("ERROR OCCURRED")
     * ```
     *
     * @template F
     * @param callable(E): F $f The callback to apply to the Err value.
     * @return Result<T, F> The original Ok or the transformed Err.
     */
    abstract public function mapErr(callable $f): Result;

    /**
     * Transforms the Ok value using a callback that returns a Result itself.
     * It works like *map* except it flattens down to the returned Result instance of the provided callback
     * in the Ok case.
     *
     * Example:
     * ```
     * $result = Result::Ok(42);
     * $newResult = $result->flatMap(fn($value) => Result::Ok($value * 2)); // $newResult is Ok(84)
     * ```
     *
     * @template U
     * @param callable(T): Result<U, E> $f The callback to apply to the Ok value.
     * @return Result<U, E> The transformed Ok or the original Err.
     */
    abstract public function flatMap(callable $f): Result;

    /**
     * Transforms the Ok value using the provided callback.
     * If the result is in the Err state, returns the provided default value.
     *
     * Example:
     * ```
     * $result = Result::Ok(42);
     * $newValue = $result->mapOr(0, fn($value) => $value * 2); // $newValue is 84
     * ```
     *
     * @template U
     * @param U $default The default value to return if the result is in the Err state.
     * @param callable(T): U $f The callback to apply to the Ok value.
     * @return U
     */
    abstract public function mapOr(mixed $default, callable $f): mixed;

    # endregion Maps
}