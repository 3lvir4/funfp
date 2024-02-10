<?php

declare(strict_types=1);

namespace Elvir4\FunFp\constructors;

use Elvir4\FunFp\Helpers\String\AsciiStringIterator;
use Elvir4\FunFp\Helpers\String\BytesIterator;
use Elvir4\FunFp\Helpers\String\Utf8CharsIterator;
use Elvir4\FunFp\Helpers\String\Utf8CodepointsIterator;
use Elvir4\FunFp\Helpers\String\Utf8LinesIterator;
use Elvir4\FunFp\Helpers\String\Utf8WordsIterator;
use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\Iter\GenerateIter;
use Elvir4\FunFp\Iter\RepeatIter;
use Elvir4\FunFp\Iter;
use Elvir4\FunFp\Option;
use Elvir4\FunFp\Pipe;
use Elvir4\FunFp\Result;
use Throwable;
use Traversable;

/**
 * These are either syntactic sugar and/or convenient iterator constructor functions.
 * Pretty much all of them are constructing objects of the library behind the scenes.
 * They allow to quickly start chaining operations on iterators constructed from primitive or user-defined types.
 */

/**
 * Creates a Pipe object that represents the chaining of provided functions.
 *
 * This function constructs a Pipe object which acts as a pipe operator, chaining the provided functions
 * in the order they are passed.
 *
 * Example usage:
 * ```
 * // Using traditional function calls:
 * $result = f4(f3(f2(f1($arg1, $arg2))));
 *
 * // Equivalent using pipe function:
 * $result = pipe(f1, f2, f3, f4)($arg1, $arg2);
 * // OR
 * $op = pipe(f1, f2, f3, f4);
 * $result = $op($arg1, $arg2);
 * ```
 *
 * It also supports chaining additional functions after creation with the `then` method:
 * ```
 * $op = pipe(f1, f2, f3, f4);
 * $op->then(f5)->then(f6);
 * ```
 *
 * @param callable ...$functions The callables to be chained.
 * @return Pipe A Pipe object representing the chained functions.
 */
function pipe(callable ...$functions): Pipe
{
    return new Pipe($functions);
}

/**
 * Returns a base IterOps interface implementation from the provided iterable.
 *
 * It allows chaining of operations on the iterator as per the IterOps interface.
 * It wraps the creation process in a Result object to handle any potential exceptions that may occur
 * during the construction.
 *
 * Example 1:
 * ```
 * $array = [1, 2, 3, 4, 5, 6];
 * $result = iter($array)
 *      ->unwrap()
 *      ->map(fn($value) => $value * 2)  // Double each value
 *      ->filter(fn($value) => $value % 3 === 0);  // Filter only multiples of 3
 *
 * foreach ($result as $value) {
 *     echo $value . PHP_EOL; // Output: 6, 12
 * }
 * ```
 *
 * Example 2:
 * ```
 * $array = [1, 2, 3, 4, 5];
 * $sum = iter($array)
 *      ->unwrap()
 *      ->map(fn($value) => $value * 2)  // Double each value
 *      ->reduce(fn($acc, $value) => $acc + $value); // Sum all values
 * echo $sum; // Output: 30 (1*2 + 2*2 + 3*2 + 4*2 + 5*2)
 * ```
 *
 * @template TKey Iterable keys type.
 * @template TVal Iterable values type.
 * @param iterable<TKey, TVal> $iterable
 * @return Result<IterOps<TKey, TVal>, Throwable>
 */
function iter(iterable $iterable): Result
{
    return Result::try(static fn() => Iter::fromIterable($iterable));
}

/**
 * Returns an IterOps implementation that yields the result of the given callback infinitely.
 * The callback function is called each time a value is requested from the iterator.
 *
 * Example:
 * ```
 * repeatWith(fn() => rand(1, 100))->take(5); // Only take the first 5 values
 * foreach ($iterator as $value) {
 *     echo $value . PHP_EOL; // Output: 5 random numbers between 1 and 100.
 * }
 * ```
 *
 * @template T Yielded values type.
 * @param callable(): T $f
 * @return Traversable<int, T>&IterOps<int, T>
 */
function repeatWith(callable $f): Traversable&IterOps
{
    return new RepeatIter($f);
}

/**
 * Creates an IterOps implementation that generates values infinitely by repeatedly applying
 * the provided $genFun callable to the previously yielded value, starting from the initial value.
 *
 * Example:
 * ```
 * $iterator = generate(1, fn($prev) => $prev * 2)->take(6); // Only take the first 6 values
 * foreach ($iterator as $value) {
 *     echo $value . PHP_EOL; // Output: first 6 powers of 2 (1, 2, 4, 8, 16, 32)
 * }
 * ```
 *
 * @template T Yielded values type.
 * @param T $initialValue The initial value to start the generation process.
 * @param callable(T): T $genFn
 * @return Traversable<int, T>&IterOps<int, T>
 */
function generate(mixed $initialValue, callable $genFn): Traversable&IterOps
{
    return new GenerateIter($initialValue, $genFn);
}

/**
 * Creates an IterOps implementation that cycles indefinitely through the elements of the given iterable.
 * It wraps the creation process in a Result object to handle any potential exceptions that may occur.
 *
 * Example:
 * ```
 * $array = [1, 2, 3];
 * $iterator = cycle($array)->take(7); // Only take the first 7 values
 * foreach ($iterator as $value) {
 *     echo $value . PHP_EOL; // Output: 1, 2, 3, 1, 2, 3, 1
 * }
 * ```
 *
 * @template TKey Iterable keys type.
 * @template TVal Iterable values type.
 * @param iterable<TKey, TVal> $iterable The iterable to cycle over.
 * @return Result<IterOps<TKey, TVal>, Throwable>
 */
function cycle(iterable $iterable): Result
{
    return iter($iterable)->map(fn($i) => $i->cycle());
}

/**
 * @param string $str
 * @return Traversable<int, int>&IterOps<int, int>
 */
function bytes(string $str): Traversable&IterOps
{
    return new Iter(new BytesIterator($str));
}

/**
 * @param string $str
 * @return Traversable<int, string>&IterOps<int, string>
 */
function chars(string $str): Traversable&IterOps
{
    return new Iter(new Utf8CharsIterator($str));
}

/**
 * @param string $str
 * @return Traversable<int, string>&IterOps<int, string>
 */
function lines(string $str): Traversable&IterOps
{
    return new Iter(new Utf8LinesIterator($str));
}

/**
 * @param string $str
 * @return Traversable<int, string>&IterOps<int, string>
 */
function words(string $str): Traversable&IterOps
{
    return new Iter(new Utf8WordsIterator($str));
}

/**
 * @param string $str
 * @return Traversable<int, int>&IterOps<int, int>
 */
function codepoints(string $str): Traversable&IterOps
{
    return new Iter(new Utf8CodepointsIterator($str));
}

/**
 * @param string $str
 * @return Traversable<int, string>&IterOps<int, string>
 */
function byteChars(string $str): Traversable&IterOps
{
    return new Iter(new AsciiStringIterator($str));
}

/**
 * @see Option::Some()
 * @template T
 * @param T $value
 * @return Option<T>
 */
function Some(mixed $value): Option
{
    return Option::Some($value);
}

/**
 * @see Option::None()
 * @return Option<mixed>
 */
function None(): Option
{
    return Option::None();
}

/**
 * @see Result::Ok()
 * @template T
 * @param T $value
 * @return Result<T, never>
 */
function Ok(mixed $value): Result
{
    return Result::Ok($value);
}

/**
 * @see Result::Err()
 * @template E
 * @param E $error
 * @return Result<never, E>
 */
function Err(mixed $error): Result
{
    return Result::Err($error);
}

