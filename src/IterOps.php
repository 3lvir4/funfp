<?php

declare(strict_types=1);

namespace Elvir4\FunFp;

use Elvir4\FunFp\Contracts\FromIterator;
use Elvir4\FunFp\Contracts\TryFromIterator;
use Elvir4\FunFp\Iter\IntersperseIter;
use Iterator;
use Throwable;

/**
 * @template-covariant TKey
 * @template-covariant TVal
 * @psalm-suppress InvalidTemplateParam
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
     * @template UVal
     * @param int $step
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): UVal $f
     * @return IterOps<TKey, UVal>
     */
    public function mapEvery(int $step, callable $f): IterOps;

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
     */
    public function values(): IterOps;

    /**
     * @return IterOps<int, TKey>
     */
    public function keys(): IterOps;

    /**
     * @template UVal
     * @param UVal $initialValue
     * @param callable(UVal, TVal, TKey): UVal $f
     * @return IterOps<TKey, UVal>
     */
    public function scan(mixed $initialValue, callable $f): IterOps;

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
     * @return IterOps<TKey, Pair<int, TVal>>
     */
    public function dedupWithCount(): IterOps;

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): mixed $f
     * @param bool $preserveKeys
     * @return IterOps<int, array<TVal>>
     */
    public function chunkBy(callable $f, bool $preserveKeys = false): IterOps;

    /**
     * @param int $count
     * @param int|null $step
     * @param bool $discard
     * @param iterable<TKey, TVal> $leftover
     * @param bool $preserveKeys
     * @return IterOps<int, array<TVal>>
     */
    public function chunkEvery(
        int $count,
        ?int $step = null,
        bool $discard = false,
        iterable $leftover = [],
        bool $preserveKeys = false
    ): IterOps;

    /**
     * @param Iterator<TKey, TVal>|IterOps<TKey, TVal> ...$iterators
     * @return IterOps<TKey, TVal>
     */
    public function concat(Iterator|IterOps ...$iterators): IterOps;

    /**
     * @template UKey
     * @template UVal
     * @param Iterator<UKey, UVal>|IterOps<UKey, UVal> $iterator
     * @return IterOps<Pair<TKey, UKey>, Pair<TVal, UVal>>
     */
    public function zip(Iterator|IterOps $iterator): IterOps;

    /**
     * @param Iterator<TKey, TVal>|IterOps<TKey, TVal> $iterator
     * @return IterOps<TKey, TVal>
     */
    public function interleave(Iterator|IterOps $iterator): IterOps;

    /**
     * @param Iterator<TKey, TVal>|IterOps<TKey, TVal> $iterator
     * @return IterOps<TKey, TVal>
     */
    public function interleaveShortest(Iterator|IterOps $iterator): IterOps;

    /**
     * @param Iterator|IterOps ...$iterators
     * @return IterOps<array<int, mixed>, array<int, mixed>>
     */
    public function zipMultiple(Iterator|IterOps ...$iterators): IterOps;

    /**
     * @template UVal
     * @param callable(array): UVal $f
     * @return IterOps<int, UVal>
     */
    public function zipWith(callable $f): IterOps;

    /**
     * @param int $start
     * @return IterOps<TKey, Pair<int, TVal>>
     */
    public function enumerate(int $start = 0): IterOps;

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return IterOps<int, TKey>
     */
    public function positions(callable $predicate): IterOps;

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
     */
    public function takeEvery(int $step): IterOps;

    /**
     * @param int $start
     * @param int $amount
     * @return IterOps<TKey, TVal>
     */
    public function slice(int $start, int $amount = -1): IterOps;

    /**
     * @param TVal $sep
     * @return IterOps<TKey, TVal>
     */
    public function intersperse(mixed $sep): IterOps;

    /**
     * @param callable(): TVal $sep
     * @return IterOps<TKey, TVal>
     */
    public function intersperseWith(callable $sep): IterOps;

    /**
     * Flattens one level of an iterator of iterators.
     * @return IterOps
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
     * @param callable(U, TVal, TKey): U $f
     * @return U
     */
    public function fold(mixed $initialValue, callable $f): mixed;

    /**
     * @param callable(TVal, TVal): TVal $f
     * @return Option<TVal>
     */
    public function reduce(callable $f): Option;

    /**
     * Iterates over without doing anything else.
     * @return void
     */
    public function run(): void;

    /**
     * @template D
     * @param class-string<D> $dest
     * @return FromIterator<D>
     */
    public function collect(string $dest): mixed;

    /**
     * @template D
     * @param class-string<D> $dest
     * @return Result<D, Throwable>
     */
    public function tryCollect(string $dest): Result;

    /**
     * @return array<TVal>
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
     * @return array<TVal>
     */
    public function toSortedArray(callable $comparator): array;

    /**
     * @return array<value-of<TVal>[]>
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
     */
    public function nth(int $n): Option;

    /**
     * @param ?callable(TVal, TVal): (int|float) $comparator
     * @return Option<TVal>
     */
    public function min(?callable $comparator): Option;

    /**
     * @param ?callable(TVal, TVal): (int|float) $comparator
     * @return Option<TVal>
     */
    public function max(?callable $comparator): Option;

    /**
     * @param string $separator
     * @return string
     */
    public function join(string $separator = ""): string;

    /**
     * @template UVal
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): UVal $f
     * @param string $separator
     * @return string
     */
    public function mapJoin(callable $f, string $separator = ""): string;

    /**
     * @param callable(TVal): bool $predicate
     * @return Option<TVal>
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
     * @return Option<TVal>
     */
    public function first(): Option;

    /**
     * @return Option<TVal>
     */
    public function last(): Option;

    /**
     * @return Option<int|float|array>
     */
    public function sum(): Option;

    /**
     * @psalm-suppress all
     * @return Option<int|float|array>
     */
    public function product(): Option;

    /**
     * @psalm-suppress all
     * @return Option<int|float>
     */
    public function average(): Option;

    # endregion Consumers

    /**
     * @return Iterator<TKey, TVal>
     */
    public function getIter(): Iterator;
}
