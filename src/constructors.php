<?php

declare(strict_types=1);

namespace Elvir4\FunFp\constructors;

use Elvir4\FunFp\Helpers\String\BytesStringIterator;
use Elvir4\FunFp\Helpers\String\Utf8StringIterator;
use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\Iter\GenerateIter;
use Elvir4\FunFp\Iter\RepeatIter;
use Elvir4\FunFp\Iter;
use Elvir4\FunFp\Option;
use Elvir4\FunFp\Pipe;
use Elvir4\FunFp\Result;
use Throwable;

/**
 * @template T
 * @param T $value
 * @param ?callable(T): void $f
 * @return T
 */
function tap(mixed $value, ?callable $f = null)
{
    if ($f !== null) $f($value);
    return $value;
}

/**
 * @param callable ...$functions
 * @return Pipe
 */
function pipe(callable ...$functions): Pipe
{
    return new Pipe($functions);
}

/**
 * Returns a base iterator from the iterable.
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $iterable
 * @return Result<IterOps<TKey, TVal>, Throwable>
 */
function iter(iterable $iterable): Result
{
    return Result::try(static fn() => Iter::fromIterable($iterable));
}

/**
 * Returns an iterator that yields the result of the given callback infinitely.
 * @template T
 * @param callable(): T $f
 * @return IterOps<int, T>
 */
function repeatWith(callable $f): IterOps
{
    return new RepeatIter($f);
}

/**
 * @template T
 * @param T $initialValue
 * @param callable(T): T $genFn
 * @return IterOps<int, T>
 */
function generate($initialValue, callable $genFn): IterOps
{
    return new GenerateIter($initialValue, $genFn);
}

/**
 * @template TKey
 * @template TVal
 * @param iterable<TKey, TVal> $iterable
 * @return Result<IterOps<TKey, TVal>, Throwable>
 */
function cycle(iterable $iterable): Result
{
    return iter($iterable)->map(fn($i) => $i->cycle());
}

/**
 * @param string $str
 * @return IterOps<int, int>
 */
function bytes(string $str): IterOps
{
    return new Iter(new BytesStringIterator($str));
}

/**
 * @param string $str
 * @return IterOps<int, string>
 */
function chars(string $str): IterOps
{
    return new Iter(new Utf8StringIterator($str));
}

/**
 * @template T
 * @param T $value
 * @return Option<T>
 */
function Some(mixed $value): Option
{
    return Option::Some($value);
}

/**
 * @return Option<mixed>
 */
function None(): Option
{
    return Option::None();
}

/**
 * @template T
 * @param T $value
 * @return Result<T, never>
 */
function Ok(mixed $value): Result
{
    return Result::Ok($value);
}

/**
 * @template E
 * @param E $error
 * @return Result<never, E>
 */
function Err(mixed $error): Result
{
    return Result::Err($error);
}

