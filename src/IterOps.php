<?php

declare(strict_types=1);

namespace Elvir4\FunFp;

use Elvir4\FunFp\Contracts\FromIterator;
use Elvir4\FunFp\Contracts\TryFromIterator;
use Iterator;
use Throwable;

/**
 * @template TKey
 * @template TVal
 */
interface IterOps
{
    # region Transformers

    /**
     * @template UVal
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): UVal $f
     * @return IterOps<TKey, UVal>
     */
    public function map(callable $f): IterOps;

    /**
     * @template UKey
     * @template UVal
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): iterable<UKey, UVal> $f
     * @return IterOps<TKey, UVal>
     */
    public function flatMap(callable $f): IterOps;

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return IterOps<TKey, TVal>
     */
    public function filter(callable $predicate): IterOps;

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return IterOps<TKey, TVal>
     */
    public function reject(callable $predicate): IterOps;

    /**
     * @return IterOps<int, TVal>
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function values(): IterOps;

    /**
     * @return IterOps<int, TKey>
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function keys(): IterOps;

    /**
     * @return IterOps<TKey, TVal>
     */
    public function unique(): IterOps;

    /**
     * @param callable(TVal): mixed $f
     * @return IterOps<TKey, TVal>
     */
    public function uniqueBy(callable $f): IterOps;

    /**
     * @return IterOps<TKey, TVal>
     */
    public function dedup(): IterOps;

    /**
     * @param callable(TVal): mixed $f
     * @return IterOps<TKey, TVal>
     */
    public function dedupBy(callable $f): IterOps;

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): mixed $f
     * @param bool $preserveKeys
     * @return IterOps<int, array<TVal>>
     */
    public function chunkBy(callable $f, bool $preserveKeys = false): IterOps;

    /**
     * @param Iterator<TKey, TVal>|Iter<TKey, TVal> ...$iterators
     * @return IterOps<TKey, TVal>
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function concat(Iterator|Iter ...$iterators): IterOps;

    /**
     * @template UKey
     * @template UVal
     * @param Iterator<UKey, UVal>|IterOps<UKey, UVal> $iterator
     * @return IterOps<list{TKey, UKey}, list{TVal, UVal}>
     * @psalm-suppress InvalidReturnType
     */
    public function zip(Iterator|IterOps $iterator): IterOps;

    /**
     * @param Iterator|IterOps ...$iterators
     * @return IterOps<array, array>
     */
    public function zipMultiple(Iterator|IterOps ...$iterators): IterOps;

    /**
     * @return IterOps<TKey, list{int, TVal}>
     */
    public function enumerate(): IterOps;

    /**
     * @param int $n
     * @return IterOps<TKey, TVal>
     */
    public function skip(int $n):  IterOps;

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return IterOps<TKey, TVal>
     */
    public function skipWhile(callable $predicate):  IterOps;

    /**
     * @param int $step
     * @return IterOps<TKey, TVal>
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function skipEvery(int $step):  IterOps;

    /**
     * @param int $n
     * @return IterOps<TKey, TVal>
     */
    public function take(int $n):  IterOps;

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return IterOps<TKey, TVal>
     */
    public function takeWhile(callable $predicate):  IterOps;

    /**
     * @param int<0, max> $step
     * @return IterOps<TKey, TVal>
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function takeEvery(int $step): IterOps;

    /**
     * @param int $start
     * @param int $amount
     * @return IterOps<TKey, TVal>
     */
    public function slice(int $start, int $amount = -1): IterOps;

    /**
     * Flattens one level of an iterator of iterators.
     * @return IterOps
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    public function flatten(): IterOps;

    /**
     * @param callable(TVal, TVal): int $comparator
     * @param bool $preserveKeys
     * @return IterOps<TKey, TVal>
     */
    public function sorted(callable $comparator, bool $preserveKeys = true): IterOps;

    /**
     * @return IterOps<TKey, TVal>
     */
    public function cycle(): IterOps;

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): void $f
     * @return IterOps<TKey, TVal>
     */
    public function each(callable $f): IterOps;

    # endregion Transformers

    # region Consumers

    /**
     * @template U
     * @param U $initialValue
     * @param callable(U, TVal): U $f
     * @return U
     */
    public function fold(mixed $initialValue, callable $f): mixed;

    /**
     * @template U
     * @param callable(U, TVal): U $f
     * @return Option<U>
     * @psalm-suppress InvalidReturnType, MixedArgumentTypeCoercion, PossiblyInvalidArgument
     */
    public function reduce(callable $f): Option;

    /**
     * Iterates over this without doing anything else.
     * @return void
     */
    public function consume(): void;

    /**
     * @template D of FromIterator
     * @param class-string<D> $dest
     * @psalm-return D
     * @psalm-suppress MixedInferredReturnType, MixedReturnTypeCoercion
     */
    public function collect(string $dest): mixed;

    /**
     * @template D of TryFromIterator
     * @param class-string<D> $dest
     * @return Result<object, Throwable>
     * @psalm-return Result<D, Throwable>
     * @psalm-suppress MixedInferredReturnType, InvalidReturnType
     */
    public function tryCollect(string $dest): Result;

    /**
     * @return TVal[]
     */
    public function toArray(): array;

    /**
     * @return array<int, TVal>
     * @psalm-return list<TVal>
     */
    public function toList(): array;

    /**
     * @param callable(TVal, TVal): int $comparator
     * @return array<int, TVal>
     * @psalm-return list<TVal>
     */
    public function toSortedList(callable $comparator): array;

    /**
     * @param callable(TVal, TVal): int $comparator
     * @return TVal[]
     */
    public function toSortedArray(callable $comparator): array;

    /**
     * @return array<value-of<TVal>[]>
     * @psalm-suppress MixedReturnTypeCoercion, MixedAssignment
     */
    public function unzip(): array;

    /**
     * @return int
     */
    public function count(): int;

    /**
     * @param int $limit
     * @return int
     */
    public function countUntil(int $limit): int;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @param int $n
     * @return Option<TVal>
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function nth(int $n): Option;

    /**
     * @param ?callable(TVal, TVal): bool $comparator
     * @return Option<TVal>
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    public function min(?callable $comparator): Option;

    /**
     * @param ?callable(TVal, TVal): bool $comparator
     * @return Option<TVal>
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    public function max(?callable $comparator): Option;

    /**
     * @param string $separator
     * @return string
     */
    public function join(string $separator = ""): string;

    /**
     * @param callable(TVal): bool $predicate
     * @return Option<TVal>
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function find(callable $predicate): Option;

    /**
     * @param callable(TVal): bool $predicate
     * @return Option<TVal>
     */
    public function findLast(callable $predicate): Option;

    /**
     * @param callable(TVal): bool $predicate
     * @return Option<TVal>
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function findKey(callable $predicate): Option;

    /**
     * @param callable(TVal): bool $predicate
     * @return Option<TVal>
     */
    public function findLastKey(callable $predicate): Option;

    /**
     * @param callable(TVal): bool $predicate
     * @return Option<int>
     */
    public function position(callable $predicate): Option;

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return bool
     */
    public function any(callable $predicate): bool;

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return bool
     */
    public function all(callable $predicate): bool;

    /**
     * @param int $count
     * @param bool $preserveKeys
     * @return TVal[]
     * @psalm-return list<TVal>
     * @psalm-suppress MixedArgumentTypeCoercion, MixedAssignment, MixedArrayOffset, MoreSpecificReturnType, InvalidArrayOffset
     */
    public function takeRandom(int $count = 1, bool $preserveKeys = false): array;

    /**
     * @return Option<TVal>
     */
    public function last(): Option;

    # endregion Consumers

    /**
     * @return Iterator<TKey, TVal>
     * @internal
     */
    public function getIter(): Iterator;
}
