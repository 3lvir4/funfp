<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Helpers;

use Countable;
use Elvir4\FunFp\Option;
use Stringable;

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
 * @template T
 * @param string|array<T> $value
 * @return Arr<T>|Str
 * @psalm-return ($value is string ? Str : Arr<T>)
 */
function of(string|array $value): Arr|Str
{
    return is_string($value)
        ? Str::of($value)
        : Arr::of($value);
}

/**
 * @template T
 * @template U
 * @param T[] $values
 * @param callable(T): U $fun
 * @return U[]
 */
function map(array $values, callable $fun): array
{
    return array_map($fun, $values);
}

/**
 * @template T
 * @param T[] $values
 * @param callable(T, array-key): bool $predicate
 * @return T[]
 */
function filter(array $values, callable $predicate): array
{
    return array_filter($values, $predicate, ARRAY_FILTER_USE_BOTH);
}

/**
 * @param array ...$arrays
 * @return array<array>
 * @psalm-suppress MixedReturnTypeCoercion
 */
function zip(array ...$arrays): array
{
    return array_map(null, ...$arrays);
}

/**
 * @param array|string|Countable $value
 *
 * @return int
 *
 * @psalm-return int<0, max>
 */
function len(array|string|Countable $value): int
{
    return is_string($value)
        ? mb_strlen($value, "UTF-8")
        : count($value);
}

/**
 * @param Stringable|string $first
 * @param Stringable|string ...$strings
 * @return string
 */
function strconcat(Stringable|string $first, Stringable|string ...$strings): string
{
    $buf = (string) $first;
    while (count($strings) > 0) {
        $buf .= array_pop($strings);
    }
    return $buf;
}

/**
 * Returns the position of $needle in $haystack.
 * @template T
 * @param iterable<T> $haystack
 * @param T $needle
 * @return Option<int>
 */
function pos(iterable $haystack, mixed $needle): Option
{
    $i = 0;
    foreach ($haystack as $value) {
        if ($value === $needle) return Option::Some($i);
        $i++;
    }
    return Option::None();
}

