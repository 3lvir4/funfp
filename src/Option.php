<?php

declare(strict_types=1);

namespace Elvir4\FunFp;

use Elvir4\FunFp\Option\None;
use Elvir4\FunFp\Option\Some;
use Generator;
use Iterator;
use RuntimeException;

/**
 * Represents an optional value that may or may not be present.
 *
 * Options are used to express the presence or absence of a value. They can be either `Some`, representing a
 * present value, or `None`, representing an absent value.
 *
 * It mimics [existing constructs of other languages](https://en.wikipedia.org/wiki/Option_type).
 *
 * @template T The type of the optional value.
 * @psalm-inheritors Some|None
 * @psalm-yield T
 */
abstract class Option
{
    # region Constructors

    /**
     * Creates a Some variant wrapping the provided value.
     *
     * Example:
     * ```
     * $option = Option::Some(42);
     * // Returns: Some(42)
     * ```
     *
     * @template U
     * @param U $value The value to wrap.
     * @return Option<U>
     */
    final public static function Some(mixed $value): Option
    {
        return new Some($value);
    }

    /**
     * Creates a None variant representing an absent value.
     *
     * Example:
     * ```
     * $option = Option::None();
     * // Returns: None
     * ```
     *
     * @return Option<mixed> None.
     */
    final public static function None(): Option
    {
        return None::getInstance();
    }

    /**
     * Wraps a value into an Option.
     * If the value is equal to null (or to a specified $nullValue), it will return None.
     *
     * Example:
     * ```
     * $optional = Option::wrap(69);
     * // Returns: Some(69)
     * $optional = Option::wrap(null);
     * // Returns: None
     * $optional = Option::wrap("", ""); // empty string specified as the "null value"
     * // Returns: None
     * ```
     *
     * @template U
     * @param U $value The value to wrap.
     * @param mixed $nullValue The value that should trigger returning None.
     * @return Option<U>
     */
    final public static function wrap(mixed $value, mixed $nullValue = null): Option
    {
        if ($value === $nullValue) return Option::None();
        return Option::Some($value);
    }

    /**
     * Wraps the return value of the given procedure into an Option.
     *
     * @see Option::wrap()
     * @template U
     * @param callable(): U $procedure The procedure to execute.
     * @param mixed $nullValue The value that should trigger returning None.
     * @return Option<U>
     */
    final public static function wrapProcedure(callable $procedure, mixed $nullValue = null): Option
    {
        $value = $procedure();
        if ($value === $nullValue) return Option::None();
        return Option::Some($value);
    }

    /**
     * It constructs an Option using the provided sequences of operations.
     * Each yield of the sequence has to produce an Option.
     * The iteration sort-circuits at the first None encountered.
     *
     * It acts as syntactic sugar over multiple *flatMap* calls.
     * Similar to [for comprehensions in Scala](https://docs.scala-lang.org/tour/for-comprehensions.html)
     * or
     * [**?** operator in Rust](https://doc.rust-lang.org/reference/expressions/operator-expr.html#the-question-mark-operator).
     *
     * Example 1:
     * ```
     * $opt = Option::with(function () {
     *     $a = yield Option::Some(1);    // Step 1: Some(1)
     *     $b = yield Option::Some(2);    // Step 2: Some(2)
     *     $c = yield Option::None();     // Step 3: None
     *     $d = yield Option::Some(4);    // This value will not be processed
     *     return $a * $b * $c * $d;      // Never reached.
     * });
     * // $opt is None
     * ```
     *
     * Example 2:
     *  ```
     *  $opt = Option::with(function () {
     *      $a = yield Option::Some(1);    // Step 1: Some(1)
     *      $b = yield Option::Some(2);    // Step 1: Some(2)
     *      $c = yield Option::Some(3);    // Step 1: Some(3)
     *      $d = yield Option::Some(4);    // Step 1: Some(4)
     *      return $a * $b * $c * $d;      // No None encounter, so it returns Some(24)
     *  });
     *  // $opt is Some(24)
     *  ```
     *
     * @template U Yielded values type.
     * @template O Return type.
     * @param callable(): Generator<int, Option<U>, U, O> $expr Generator expression to run.
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
     * Executes the given procedure and wraps the result in an Option.
     * If the procedure succeeds, returns Some with the result. Otherwise, returns None.
     *
     * This constructor is useful when you don't care about the details of the potential exceptions that the procedure
     * may throw and/or want them to be regarded as "absence of a value".
     *
     * Example:
     * ```
     * // Define a procedure that may throw an exception
     * function riskyOperation(): int {
     *     $result = random_int(0, 1); // Randomly return 0 or 1
     *     if ($result === 0) {
     *         throw new Exception('Operation failed!');
     *     }
     *     return $result;
     * }
     *
     * // Try the risky operation and handle the result as an Option
     * $option = Option::try('riskyOperation');
     * if ($option->isSome()) {
     *     // do something
     * }
     * ```
     *
     * @template U The type of the return value.
     * @param callable(): U $procedure The procedure to execute.
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
     * Unwraps the value from the Option if it's Some.
     * If the Option is Some, this method returns the wrapped value.
     * If the Option is None, it throws.
     *
     * @psalm-mutation-free
     * @return T The unwrapped value.
     * @throws RuntimeException When variant is None.
     */
    abstract public function unwrap(): mixed;

    /**
     * Get the value out of the Option if `Some` variant.
     * Returns the provided default value otherwise.
     *
     * @psalm-mutation-free
     * @param T $default The default value to return if the option is None.
     * @return T
     */
    abstract public function unwrapOr(mixed $default): mixed;

    /**
     * Get the value out of the Option if `Some` variant.
     * Returns the result of the provided callable otherwise.
     *
     * @param callable(): T $f The fallback to execute if None.
     * @return T
     */
    abstract public function unwrapOrElse(callable $f): mixed;

    /**
     * Retrieves the wrapped value from the Option, returning it if it's Some, or null if it's None.
     *
     * This method is useful when you want to handle a null directly instead of a None in certain contexts.
     * For using the optional chaining operator directly for example.
     *
     * @return T|null null if None variant
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
     * Maps the value of the Option using the provided function if the Option is Some.
     * If the Option is None, nothing happens.
     *
     * Example:
     * ```
     *  $option = Option::Some(5);
     *  $mapped = $option->map(fn($value) => $value * 2);
     *  // $mapped is Some(10)
     *
     *  $option = Option::None();
     *  $mapped = $option->map(fn($value) => $value * 2);
     *  // $mapped is None()
     *  ```
     *
     * @template O
     * @param callable(T): O $f Mapping function.
     * @return Option<O> The transformed Some variant or None.
     */
    abstract public function map(callable $f): Option;

    /**
     * Maps the value of the Option using the provided function if the Option is Some.
     * It works like *map* except it flattens down to the returned Option instance of the provided callback
     * if it's Some.
     *
     * Example:
     * ```
     *  $option = Option::Some(5);
     *  $mapped = $option->flatMap(fn($value) => Option::Some($value * 2));
     *  // $mapped is Some(10)
     *
     *  $option = Option::None();
     *  $mapped = $option->flatMap(fn($value) => Option::Some($value * 2));
     *  // $mapped is None()
     *  ```
     *
     * @template O
     * @param callable(T): Option<O> $f The callback to apply.
     * @return Option<O> The transformed Some variant or None.
     */
    abstract public function flatMap(callable $f): Option;

    /**
     * Filter the wrapped Some value with the provided callable.
     *
     * ```
     *  $option = Option::Some(5);
     *  $filteredOption = $option->filter(fn($value) => $value > 3);
     *  // $filteredOption is Some(5)
     *
     *  $filteredOption = $option->filter(fn($value) => $value > 10);
     *  // $filteredOption is None()
     *
     *  $option = Option::None();
     *  $filteredOption = $option->filter(fn($value) => $value > 3);
     *  // $filteredOption is None()
     *  ```
     *
     * @param callable(T): bool $predicate
     * @return Option<T>
     */
    abstract public function filter(callable $predicate): Option;

    /**
     * Maps the value of the Option using the provided callable if the Option is Some.
     * If the Option is None, returns the provided default value.
     *
     * Example:
     * ```
     *  $option = Option::Some(5);
     *  $mapped = $option->mapOr(0, fn($value) => $value * 2);
     *  // $mapped is 10
     *
     *  $option = Option::None();
     *  $mapped = $option->mapOr(0, fn($value) => $value * 2);
     *  // $mapped is 0
     *  ```
     *
     * @template O
     * @param O $default The default value to return if the Option is None.
     * @param callable(T): O $f The mapping function.
     * @return O The mapped value or the default.
     */
    abstract public function mapOr(mixed $default, callable $f): mixed;

    /**
     * Flatten one level of the option if it contains another Option.
     * - Option<Option<T>> becomes Option<T>
     * - Option<T> stays Option<T>
     *
     * @template U
     * @return Option<T|U>
     * @psalm-return (T is Option<U> ? Option<U> : Option<T>)
     */
    abstract public function flatten(): Option;

    # endregion Maps
}