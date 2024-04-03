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
     * Creates a new iterator which yields the results of calling the provided callable on every
     * item of this iterator. The callable is given at most 3 arguments, in order: the value, the key and
     * the iterator itself.
     *
     * Example:
     * ```
     * $items = iter([0,1,2,3,4])->unwrap();
     * $mapped = $items->map(fn($n) => $n * 2);
     * // $mapped->toList() results in [0,2,4,6,8]
     * $mapped = $mapped->map(fn($n,$i) => $n + $i);
     * // $mapped->toList() results in [0,3,6,9,12]
     * ```
     *
     * @template UVal
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): UVal $f
     * @return IterOps<TKey, UVal>
     */
    public function map(callable $f): IterOps;

    /**
     * Acts like {@see IterOps::map()} except it applies the callable every multiple of the given $step argument.
     * The first item of the iterator is always mapped.
     *
     * Example:
     * ```
     * $items = iter([0,1,2,3,4,5,6])->unwrap();
     * $mapped = $items->mapEvery(2, fn($n) => $n * 2);
     * // $mapped->toList() results in [0,1,4,3,8,5,12]
     * ```
     *
     * @template UVal
     * @param int $step
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): UVal $f
     * @return IterOps<TKey, UVal>
     */
    public function mapEvery(int $step, callable $f): IterOps;

    /**
     * Creates a new iterator by applying a transformation function to each item of this iterator and
     * flattening the resulting iterators into one. The transformation function is given at most 3 arguments,
     * in order: the value, the key, and the iterator itself. It must return an iterable (e.g., an array or an iterator)
     * that will be flattened into the resulting iterator.
     *
     * Example:
     * ```
     * $items = iter([1, 2, 3])->unwrap();
     * $flatMapped = $items->flatMap(function ($n) {
     *     return [$n, $n * 2];
     * });
     * // $flatMapped->toList() results in [1, 2, 2, 4, 3, 6]
     * ```
     *
     * @template UKey
     * @template UVal
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): iterable<UKey, UVal> $f
     * @return IterOps<TKey, UVal>
     */
    public function flatMap(callable $f): IterOps;

    /**
     * Creates a new iterator containing only the items from this iterator that satisfy the given predicate.
     * The predicate function is given at most 3 arguments, in order: the value, the key, and the iterator itself.
     * If the predicate returns true, the item will be included in the resulting iterator; otherwise,
     * it will be excluded.
     *
     * Example:
     * ```
     * $items = iter([1, 2, 3, 4, 5])->unwrap();
     * $filtered = $items->filter(fn($n) => $n % 2 === 0);
     * // $filtered->toList() results in [2, 4]
     * ```
     *
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return IterOps<TKey, TVal>
     */
    public function filter(callable $predicate): IterOps;

    /**
     * Works like {@see IterOps::filter()} except that it excludes the items that satisfy the given predicate.
     *
     * Example:
     * ```
     * $items = iter([1, 2, 3, 4, 5])->unwrap();
     * $rejected = $items->reject(fn($n) => $n % 2 === 0);
     * // $rejected->toList() results in [1, 3, 5]
     * ```
     *
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return IterOps<TKey, TVal>
     */
    public function reject(callable $predicate): IterOps;

    /**
     * Creates an iterator over the values only, replacing the keys by a 0-based index.
     *
     * @return IterOps<int, TVal>
     */
    public function values(): IterOps;

    /**
     * Creates an iterator over the keys of the iterator.
     *
     * @return IterOps<int, TKey>
     */
    public function keys(): IterOps;

    /**
     * Creates a new iterator that applies the given function to each element, emits the result, and uses the same result
     * as the accumulator for the next computation. The given $initialValue is used as the starting value for the accumulator.
     * The function is given at most 3 arguments, in order: the accumulator value, the value of the current element, and the key.
     * It needs to return the new accumulator value for the next computation.
     *
     * Example:
     * ```
     * $items = iter([1, 2, 3, 4, 5, 6])->unwrap();
     * $scanned = $items->scan(0, fn($acc, $val) => $acc + $val);
     * // $scanned->toList() results in [1, 3, 6, 10, 15, 21]
     * ```
     * @template UVal
     * @param UVal $initialValue
     * @param callable(UVal, TVal, TKey): UVal $f
     * @return IterOps<TKey, UVal>
     */
    public function scan(mixed $initialValue, callable $f): IterOps;

    /**
     * Creates a new iterator that only emits values if they are unique.
     * It uses loose comparison if the values are objects (otherwise, it uses strict comparison).
     *
     * Example:
     * ```
     * $items = iter([0,1,0,0,0,1,1,2,0,1,1,0,1])->unwrap();
     * $res = $items->unique();
     * // $res->toList() results in [0,1,2]
     * ```
     *
     * @return IterOps<TKey, TVal>
     */
    public function unique(): IterOps;

    /**
     * Works like {@see IterOps::unique()} except that it only emits values if they are unique according to the result of applying
     * the given callable $f to each value. Values are considered unique if the result of applying $f to them
     * is distinct from the result of applying $f to any previously emitted value.
     *
     * Example:
     * ```
     * $items = iter([['id' => 1, 'name' => 'John'], ['id' => 2, 'name' => 'Jane'], ['id' => 1, 'name' => 'John']]);
     * $uniqueById = $items->uniqueBy(fn($item) => $item['id']);
     * // $uniqueById->toArray() results in [['id' => 1, 'name' => 'John'], ['id' => 2, 'name' => 'Jane']]
     * ```
     *
     * @param callable(TVal): mixed $f
     * @return IterOps<TKey, TVal>
     */
    public function uniqueBy(callable $f): IterOps;

    /**
     * Creates a new iterator that only emits items if they are different from the previous.
     * It uses loose comparison if the values are objects (otherwise, it uses strict comparison).
     *
     * Example:
     * ```
     * $items = iter([1, 2, 3, 3, 2, 1])->unwrap();
     * $deduplicated = $items->dedup();
     * // $deduplicated->toList() results in [1, 2, 3, 2, 1]
     * ```
     *
     * @return IterOps<TKey, TVal>
     */
    public function dedup(): IterOps;

    /**
     * Works like {@see IterOps::dedup()} except that it only emits items if
     * the result of calling the given function $f on the value is different from the result of calling $f
     * on the previously emitted value.
     *
     * Example:
     * ```
     * $items = iter([[1, 'x'], [2, 'y'], [2, 'z'], [1, 'x']])->unwrap();
     * $deduplicated = $items->dedupBy(fn($item) => $item[0]);
     * // $deduplicated->toList() results in [[1, 'x'], [2, 'y'], [1, 'x']]
     * ```
     *
     * @param callable(TVal): mixed $f
     * @return IterOps<TKey, TVal>
     */
    public function dedupBy(callable $f): IterOps;

    /**
     * Creates a new iterator that emits pairs containing each unique element from the original iterator along with the count.
     * The count represents the number of times the element appeared consecutively in the original iterator before being
     * deduplicated. It uses loose comparison if the values are objects (otherwise, it uses strict comparison).
     *
     * Example:
     * ```
     * $items = iter([1, 1, 2, 2, 2, 3, 4, 4, 4, 4])->unwrap();
     * $deduplicatedWithCount = $items->dedupWithCount();
     * // $deduplicatedWithCount->toList() results in [pair(2, 1), pair(3, 2), pair(1, 3), pair(4, 4)]
     * ```
     *
     * @return IterOps<TKey, Pair<int, TVal>>
     */
    public function dedupWithCount(): IterOps;

    /**
     * Creates a new iterator that chunks elements of the original iterator into arrays based on the result of applying
     * the given function $f to each element. Elements are chunked together if they produce the same result when
     * $f is applied to them, and only consecutive elements with the same result are grouped together.
     * If $preserveKeys is set to true, the keys of the original iterator are preserved in the resulting chunks.
     *
     * Example:
     * ```
     * $items = iter([1, 2, 4, 7, 9, 12, 15, 17])->unwrap();
     * $chunked = $items->chunkBy(fn($item) => $item % 2 === 0);
     * // $chunked->toList() results in [[1], [2, 4], [7, 9], [12], [15, 17]]
     * ```
     *
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): mixed $f
     * @param bool $preserveKeys
     * @return IterOps<int, array<TVal>>
     */
    public function chunkBy(callable $f, bool $preserveKeys = false): IterOps;

    /**
     * Streams the iterator in chunks, containing $count elements each, where each new chunk starts $step elements into the iterator.
     * $step is optional and, if not passed, defaults to $count, i.e., chunks do not overlap.
     * Chunking will stop as soon as the iterator ends or when it emits an incomplete chunk.
     * If the last chunk does not have $count elements to fill the chunk, elements are taken from $leftover to fill in the chunk.
     * If $leftover does not have enough elements to fill the chunk, then a partial chunk is returned with less than $count elements.
     * If $discard is true, the last chunk is discarded unless it has exactly $count elements.
     * If $preserveKeys is set to true, the keys of the original iterator are preserved in the resulting chunks.
     *
     * Example:
     * ```
     * $items = iter([1, 2, 3, 4, 5, 6])->unwrap();
     * $chunked = $items->chunkEvery(2);
     * // $chunked->toList() results in [[1, 2], [3, 4], [5, 6]]
     *
     * $chunked = $items->chunkEvery(2, 1);
     * // $chunked->toList() results in [[1, 2], [2, 3], [3, 4], [4, 5], [5, 6], [6]]
     *
     * $items = iter([1, 2, 3, 4, 5, 6, 7])->unwrap();
     * $chunked = $items->chunkEvery(3, leftover: [8, 9]);
     * // $chunked->toList() results in [[1, 2, 3], [4, 5, 6], [7, 8, 9]]
     *
     * $chunked = $items->chunkEvery(3, discard: true);
     * // $chunked->toList() results in [[1, 2, 3], [4, 5, 6]]
     * ```
     *
     * @param int $count The number of elements in each chunk.
     * @param int|null $step The number of elements to step between each chunk. Defaults to $count if not provided.
     * @param bool $discard Whether to discard the last chunk if it does not have exactly $count elements.
     * @param iterable<TKey, TVal> $leftover The iterable to use for filling incomplete chunks.
     * @param bool $preserveKeys Whether to preserve keys in the resulting chunks.
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
     * Concatenates multiple iterators or IterOps instances into a single one.
     *
     * Example:
     * ```
     * $items1 = iter([1, 2, 3])->unwrap();
     * $items2 = iter([4, 5, 6])->unwrap();
     * $concatenated = $items1->concat($items2);
     * // $concatenated->toList() results in [1, 2, 3, 4, 5, 6]
     * ```
     *
     * @param Iterator<TKey, TVal>|IterOps<TKey, TVal> ...$iterators
     * @return IterOps<TKey, TVal>
     */
    public function concat(Iterator|IterOps ...$iterators): IterOps;

    /**
     * Zips the elements of this iterator with the elements of another iterator or IterOps instance.
     *
     * Example:
     * ```
     * $items1 = iter(['a', 'b', 'c'])->unwrap();
     * $items2 = iter([1, 2, 3])->unwrap();
     * $zipped = $items1->zip($items2);
     * // $zipped->toList() results in [pair('a', 1), pair('b', 2), pair('c', 3)]
     * ```
     *
     * @template UKey
     * @template UVal
     * @param Iterator<UKey, UVal>|IterOps<UKey, UVal> $iterator
     * @return IterOps<Pair<TKey, UKey>, Pair<TVal, UVal>>
     */
    public function zip(Iterator|IterOps $iterator): IterOps;

    /**
     * Interleaves the elements of this iterator with the elements of another iterator or IterOps instance.
     * Elements are alternated between both iterators until both of them runs out of elements.
     *
     * Example:
     * ```
     * $numbers = iter([1, 2, 3])->unwrap();
     * $letters = iter(['a', 'b', 'c', 'd', 'e'])->unwrap();
     * $interleaved = $numbers->interleave($letters);
     * // $interleaved->toList() results in [1, 'a', 2, 'b', 3, 'c', 'd', 'e']
     * ```
     *
     * @param Iterator<TKey, TVal>|IterOps<TKey, TVal> $iterator
     * @return IterOps<TKey, TVal>
     */
    public function interleave(Iterator|IterOps $iterator): IterOps;

    /**
     * Interleaves the elements of this iterator with the elements of another iterator or IterOps instance.
     * Elements are alternated between both iterators until at least one of them runs out of elements.
     *
     * Example:
     * ```
     * $numbers = iter([1, 2, 3])->unwrap();
     * $letters = iter(['a', 'b'])->unwrap();
     * $interleaved = $numbers->interleaveShortest($letters);
     * // $interleaved->toList() results in [1, 'a', 2, 'b']
     * ```
     *
     * @param Iterator<TKey, TVal>|IterOps<TKey, TVal> $iterator
     * @return IterOps<TKey, TVal>
     */
    public function interleaveShortest(Iterator|IterOps $iterator): IterOps;

    /**
     * Works like {@see IterOps::zip()} but allow for more than one iterator/IterOps instance.
     *
     * @param Iterator|IterOps ...$iterators
     * @return IterOps<array<int, mixed>, array<int, mixed>>
     */
    public function zipMultiple(Iterator|IterOps ...$iterators): IterOps;

    /**
     * Lazily zips corresponding elements of iterable elements into a new iterator,
     * transforming them with the provided callable as it goes.
     *
     * The callable is invoked with an array containing the current elements from each iterables,
     * in the order they were provided. The resulting value from the callable is emitted by the new iterator.
     *
     * As such, $this needs to be iterating over iterables, if not, it will crash.
     *
     * Example:
     * ```
     * $numbers = iter([1, 2, 3])->unwrap();
     * $letters = iter(['a', 'b', 'c'])->unwrap();
     * $zipped = $numbers->zipWith(fn($arr) => implode('-', $arr));
     * // $zipped->toList() results in ['1-a', '2-b', '3-c']
     * ```
     *
     * @template UVal
     * @param callable(array): UVal $f
     * @return IterOps<int, UVal>
     */
    public function zipWith(callable $f): IterOps;

    /**
     * Creates a new IterOps instance where each element is a pair consisting of the index and the corresponding value
     * from this iterator, starting from the specified integer (if given).
     *
     * Example:
     * ```
     * $items = iter(['a', 'b', 'c'])->unwrap();
     * $enumerated = $items->enumerate();
     * // $enumerated->toList() results in [pair(0, 'a'), pair(1, 'b'), pair(2, 'c')]
     * ```
     *
     * @param int $start
     * @return IterOps<TKey, Pair<int, TVal>>
     */
    public function enumerate(int $start = 0): IterOps;

    /**
     * Creates a new IterOps instance where each element is the index of a value from this instance
     * that satisfies the given predicate function.
     *
     * Example:
     * ```
     * $items = iter([1, 2, 3, 4, 5])->unwrap();
     * $positions = $items->positions(fn($val) => $val % 2 === 0);
     * // $positions->toList() results in [1, 3]
     * ```
     *
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return IterOps<int, TKey>
     */
    public function positions(callable $predicate): IterOps;

    /**
     * Creates a new IterOps instance that skips the first $n elements of this iterator.
     *
     * Example:
     * ```
     * $items = iter([1, 2, 3, 4, 5])->unwrap();
     * $skipped = $items->skip(2);
     * // $skipped->toList() results in [3, 4, 5]
     * ```
     *
     * @param int $n
     * @return IterOps<TKey, TVal>
     */
    public function skip(int $n):  IterOps;

    /**
     * Creates a new IterOps instance that skips elements from the start until the first element
     * that doesn't satisfy the given predicate.
     *
     * Example:
     * ```
     * $items = iter([1, 2, 3, 4, 5])->unwrap();
     * $skipped = $items->skipWhile(fn($val) => $val < 3);
     * // $skipped->toList() results in [3, 4, 5]
     * ```
     *
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return IterOps<TKey, TVal>
     */
    public function skipWhile(callable $predicate):  IterOps;

    /**
     * Creates a new IterOps instance that skips every $step-th element of this iterator.
     * The first element is always skipped unless $step is equal to 0. $step must be positive.
     *
     * Example:
     * ```
     * $items = iter([1, 2, 3, 4, 5])->unwrap();
     * $skipped = $items->skipEvery(2);
     * // $skipped->toList() results in [2, 4]
     * ```
     *
     * @param int $step
     * @return IterOps<TKey, TVal>
     */
    public function skipEvery(int $step):  IterOps;

    /**
     * Creates a new IterOps instance that yields the first $n elements of this instance.
     *
     * Example:
     * ```
     * $items = iter([1, 2, 3, 4, 5])->unwrap();
     * $taken = $items->take(3);
     * // $taken->toList() results in [1, 2, 3]
     * ```
     *
     * @param int $n
     * @return IterOps<TKey, TVal>
     */
    public function take(int $n):  IterOps;

    /**
     * Creates a new IterOps instance that yields elements from the start until the first element
     * that doesn't satisfy the given predicate function.
     *
     * Example:
     * ```
     * $items = iter([1, 2, 3, 4, 5])->unwrap();
     * $taken = $items->takeWhile(fn($val) => $val < 4);
     * // $taken->toList() results in [1, 2, 3]
     * ```
     *
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return IterOps<TKey, TVal>
     */
    public function takeWhile(callable $predicate):  IterOps;

    /**
     * Creates a new IterOps instance that yields every $step-th element of this instance.
     * The first element is always included unless $step is equal to 0. $step must be positive.
     *
     * Example:
     * ```
     * $items = iter([1, 2, 3, 4, 5])->unwrap();
     * $taken = $items->takeEvery(2);
     * // $taken->toList() results in [1, 3, 5]
     * ```
     *
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
