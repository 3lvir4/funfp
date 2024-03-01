<?php

declare(strict_types=1);

namespace Elvir4\FunFp;

use ArrayAccess;
use Elvir4\FunFp\Contracts\FromIterator;
use Elvir4\FunFp\Contracts\TryFromIterator;
use Elvir4\FunFp\Iter\ChunkByIter;
use Elvir4\FunFp\Iter\ChunkEvery;
use Elvir4\FunFp\Iter\ConcatIter;
use Elvir4\FunFp\Iter\CycleIter;
use Elvir4\FunFp\Iter\DedupByIter;
use Elvir4\FunFp\Iter\DedupIter;
use Elvir4\FunFp\Iter\DedupWithCountIter;
use Elvir4\FunFp\Iter\EachIter;
use Elvir4\FunFp\Iter\EmptyIter;
use Elvir4\FunFp\Iter\EnumerateIter;
use Elvir4\FunFp\Iter\FilterIter;
use Elvir4\FunFp\Iter\FlatMapIter;
use Elvir4\FunFp\Iter\FlattenIter;
use Elvir4\FunFp\Iter\InterleaveIter;
use Elvir4\FunFp\Iter\InterleaveShortestIter;
use Elvir4\FunFp\Iter\IntersperseIter;
use Elvir4\FunFp\Iter\IntersperseWithIter;
use Elvir4\FunFp\Iter\KeysIter;
use Elvir4\FunFp\Iter\MapEveryIter;
use Elvir4\FunFp\Iter\MapIter;
use Elvir4\FunFp\Iter\PositionsIter;
use Elvir4\FunFp\Iter\ScanIter;
use Elvir4\FunFp\Iter\SkipEveryIter;
use Elvir4\FunFp\Iter\SkipIter;
use Elvir4\FunFp\Iter\SkipWhileIter;
use Elvir4\FunFp\Iter\SliceIter;
use Elvir4\FunFp\Iter\SortedIter;
use Elvir4\FunFp\Iter\TakeEveryIter;
use Elvir4\FunFp\Iter\TakeIter;
use Elvir4\FunFp\Iter\TakeWhileIter;
use Elvir4\FunFp\Iter\UniqueByIter;
use Elvir4\FunFp\Iter\UniqueIter;
use Elvir4\FunFp\Iter\ValuesIter;
use Elvir4\FunFp\Iter\ZipIter;
use Elvir4\FunFp\Iter\ZipMultipleIter;
use Elvir4\FunFp\Iter\ZipWithIter;
use Exception;
use Iterator;
use RuntimeException;
use SplObjectStorage;
use Throwable;
use function Elvir4\FunFp\Helpers\op;
use function min;
use function uasort;
use function usort;

/**
 * @uses IterOps
 * @template-covariant TKey
 * @template-covariant TVal
 * @psalm-require-implements IterOps
 */
trait IterTrait
{
    # region Transformers

    /**
     * @template UVal
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): UVal $f
     * @return IterOps<TKey, UVal>
     */
    public function map(callable $f): IterOps
    {
        return new MapIter($this->getIter(), $f);
    }

    /**
     * @template UVal
     * @param int $step
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): UVal $f
     * @return IterOps<TKey, UVal>
     */
    public function mapEvery(int $step, callable $f): IterOps
    {
        return new MapEveryIter($this->getIter(), $step, $f);
    }

    /**
     * @template UKey
     * @template UVal
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): iterable<UKey, UVal> $f
     * @return IterOps<TKey, UVal>
     */
    public function flatMap(callable $f): IterOps
    {
        return new FlatMapIter($this->getIter(), $f);
    }

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return IterOps<TKey, TVal>
     */
    public function filter(callable $predicate): IterOps
    {
        return new FilterIter($this->getIter(), $predicate);
    }

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return IterOps<TKey, TVal>
     */
    public function reject(callable $predicate): IterOps
    {
        return new FilterIter(
            $this->getIter(),
            /**
             * @param TVal $v
             * @param TKey $k
             * @param Iterator<TKey, TVal> $i
             */
            static fn($v, $k, Iterator $i) => !$predicate($v, $k, $i)
        );
    }

    /**
     * @return IterOps<int, TVal>
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function values(): IterOps
    {
        return new ValuesIter($this->getIter());
    }

    /**
     * @return IterOps<int, TKey>
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function keys(): IterOps
    {
        return new KeysIter($this->getIter());
    }

    /**
     * @template UVal
     * @param UVal $initialValue
     * @param callable(UVal, TVal, TKey): UVal $f
     * @return IterOps<TKey, UVal>
     */
    public function scan(mixed $initialValue, callable $f): IterOps
    {
        return new ScanIter($this->getIter(), $initialValue, $f);
    }

    /**
     * @return IterOps<TKey, TVal>
     */
    public function unique(): IterOps
    {
        return new UniqueIter($this->getIter());
    }

    /**
     * @param callable(TVal): mixed $f
     * @return IterOps<TKey, TVal>
     */
    public function uniqueBy(callable $f): IterOps
    {
        return new UniqueByIter($this->getIter(), $f);
    }

    /**
     * @return IterOps<TKey, TVal>
     */
    public function dedup(): IterOps
    {
        return new DedupIter($this->getIter());
    }

    /**
     * @param callable(TVal): mixed $f
     * @return IterOps<TKey, TVal>
     */
    public function dedupBy(callable $f): IterOps
    {
        return new DedupByIter($this->getIter(), $f);
    }

    /**
     * @return IterOps<TKey, Pair<int, TVal>>
     */
    public function dedupWithCount(): IterOps
    {
        return new DedupWithCountIter($this->getIter());
    }

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): mixed $f
     * @param bool $preserveKeys
     * @return IterOps<int, array<TVal>>
     */
    public function chunkBy(callable $f, bool $preserveKeys = false): IterOps
    {
        return new ChunkByIter($this->getIter(), $f, $preserveKeys);
    }

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
    ): IterOps {
        return new ChunkEvery($this->getIter(), $count, $step ?? $count, $discard, $leftover, $preserveKeys);
    }

    /**
     * @param Iterator<TKey, TVal>|IterOps<TKey, TVal> ...$iterators
     * @return IterOps<TKey, TVal>
     */
    public function concat(Iterator|IterOps ...$iterators): IterOps
    {
        for ($i = 0; $i < count($iterators); $i++) {
            if ($iterators[$i] instanceof IterOps) {
                $iterators[$i] = $iterators[$i]->getIter();
            }
        }

        /** @var Iterator<TKey, TVal>[] $iterators */
        return new ConcatIter($this->getIter(), $iterators);
    }

    /**
     * @template UKey
     * @template UVal
     * @param Iterator<UKey, UVal>|IterOps<UKey, UVal> $iterator
     * @return IterOps<Pair<TKey, UKey>, Pair<TVal, UVal>>
     */
    public function zip(Iterator|IterOps $iterator): IterOps
    {
        return new ZipIter(
            $this->getIter(),
            $iterator instanceof IterOps
                ? $iterator->getIter()
                : $iterator
        );
    }

    /**
     * @param Iterator<TKey, TVal>|IterOps<TKey, TVal> $iterator
     * @return IterOps<TKey, TVal>
     */
    public function interleave(Iterator|IterOps $iterator): IterOps
    {
        return new InterleaveIter(
            $this->getIter(),
            $iterator instanceof IterOps
                ? $iterator->getIter()
                : $iterator
        );
    }

    /**
     * @param Iterator<TKey, TVal>|IterOps<TKey, TVal> $iterator
     * @return IterOps<TKey, TVal>
     */
    public function interleaveShortest(Iterator|IterOps $iterator): IterOps
    {
        return new InterleaveShortestIter(
            $this->getIter(),
            $iterator instanceof IterOps
                ? $iterator->getIter()
                : $iterator
        );
    }

    /**
     * @param Iterator|IterOps ...$iterators
     * @return IterOps<array<int, mixed>, array<int, mixed>>
     */
    public function zipMultiple(Iterator|IterOps ...$iterators): IterOps
    {
        $g = static fn (Iterator|IterOps $iter): Iterator => $iter instanceof IterOps ? $iter->getIter() : $iter;
        return new ZipMultipleIter($this->getIter(), ...array_map($g, $iterators));
    }

    /**
     * @template UVal
     * @param callable(array): UVal $f
     * @return IterOps<int, UVal>
     */
    public function zipWith(callable $f): IterOps
    {
        $i = $this->getIter(); $i->rewind();
        if (!is_iterable($i->current())) {
            throw new RuntimeException("Call of `IterOps::zipWith` on iterator over non-iterables.");
        }
        /** @var Iterator<iterable> $i */
        return new ZipWithIter($i, $f);
    }

    /**
     * @param int $start
     * @return IterOps<TKey, Pair<int, TVal>>
     */
    public function enumerate(int $start = 0): IterOps
    {
        return new EnumerateIter($this->getIter(), $start);
    }

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return IterOps<int, TKey>
     */
    public function positions(callable $predicate): IterOps
    {
        return new PositionsIter($this->getIter(), $predicate);
    }

    /**
     * @param int $n
     * @return IterOps<TKey, TVal>
     */
    public function skip(int $n): IterOps
    {
        return new SkipIter($this->getIter(), $n);
    }

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return IterOps<TKey, TVal>
     */
    public function skipWhile(callable $predicate): IterOps
    {
        return new SkipWhileIter($this->getIter(), $predicate);
    }

    /**
     * @param int $step
     * @return IterOps<TKey, TVal>
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function skipEvery(int $step): IterOps
    {
        if ($step === 1) return new EmptyIter();
        return new SkipEveryIter($this->getIter(), $step);
    }

    /**
     * @param int $n
     * @return IterOps<TKey, TVal>
     */
    public function take(int $n): IterOps
    {
        return new TakeIter($this->getIter(), $n);
    }

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return IterOps<TKey, TVal>
     */
    public function takeWhile(callable $predicate): IterOps
    {
        return new TakeWhileIter($this->getIter(), $predicate);
    }

    /**
     * @param int<0, max> $step
     * @return IterOps<TKey, TVal>
     * @psalm-suppress MixedReturnTypeCoercion
     */
    public function takeEvery(int $step): IterOps
    {
        if ($step === 0) return new EmptyIter();
        return new TakeEveryIter($this->getIter(), $step);
    }

    /**
     * @param int $start
     * @param int $amount
     * @return IterOps<TKey, TVal>
     */
    public function slice(int $start, int $amount = -1): IterOps
    {
        return new SliceIter($this->getIter() ,$start, $amount);
    }

    /**
     * @param TVal $sep
     * @return IterOps<TKey, TVal>
     */
    public function intersperse(mixed $sep): IterOps
    {
        return new IntersperseIter($this->getIter(), $sep);
    }

    /**
     * @param callable(): TVal $sep
     * @return IterOps<TKey, TVal>
     */
    public function intersperseWith(callable $sep): IterOps
    {
        return new IntersperseWithIter($this->getIter(), $sep);
    }

    /**
     * Flattens one level of an iterator of iterators.
     * @return IterOps
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    public function flatten(): IterOps
    {
        return new FlattenIter($this->getIter());
    }

    /**
     * @param callable(TVal, TVal): int $comparator
     * @param bool $preserveKeys
     * @return IterOps<TKey, TVal>
     */
    public function sorted(callable $comparator, bool $preserveKeys = true): IterOps
    {
        return new SortedIter($this->getIter(), $comparator, $preserveKeys);
    }

    /**
     * @return IterOps<TKey, TVal>
     */
    public function cycle(): IterOps
    {
        return new CycleIter($this->getIter());
    }

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): void $f
     * @return IterOps<TKey, TVal>
     */
    public function each(callable $f): IterOps
    {
        return new EachIter($this->getIter(), $f);
    }

    # endregion Transformers

    # region Consumers

    /**
     * @template U
     * @param U $initialValue
     * @param callable(U, TVal, TKey): U $f
     * @return U
     */
    public function fold(mixed $initialValue, callable $f): mixed
    {
        $acc = $initialValue;
        foreach ($this->getIter() as $key => $value) {
            $acc = call_user_func($f, $acc, $value, $key);
        }
        return $acc;
    }

    /**
     * @param callable(TVal, TVal): TVal $f
     * @return Option<TVal>
     */
    public function reduce(callable $f): Option
    {
        $iter = $this->getIter(); $iter->rewind();
        if (!$iter->valid()) return Option::None();
        $acc = $iter->current();
        $iter->next();
        while ($iter->valid()) {
            $acc = call_user_func($f, $acc, $iter->current());
            $iter->next();
        }
        return Option::Some($acc);
    }

    /**
     * Iterates over without doing anything else.
     * @return void
     */
    public function run(): void
    {
        $iter = $this->getIter(); $iter->rewind();
        while ($iter->valid()) {
            $iter->key();
            $iter->current();
            $iter->next();
        }
    }

    /**
     * @template D
     * @param class-string<D> $dest
     * @return FromIterator<D>
     * @psalm-suppress MixedInferredReturnType, MixedMethodCall
     */
    public function collect(string $dest): mixed
    {
        return $dest::fromIterator($this->getIter());
    }

    /**
     * @template D
     * @param class-string<D> $dest
     * @return Result<D, Throwable>
     * @psalm-suppress MixedInferredReturnType, MixedMethodCall
     */
    public function tryCollect(string $dest): Result
    {
        return $dest::tryFromIterator($this->getIter());
    }

    /**
     * @return array<TVal>
     */
    public function toArray(): array
    {
        return iterator_to_array($this->getIter());
    }

    /**
     * @return array<int, TVal>
     * @psalm-return list<TVal>
     */
    public function toList(): array
    {
        return iterator_to_array($this->getIter(), false);
    }

    /**
     * @param callable(TVal, TVal): int $comparator
     * @return array<int, TVal>
     * @psalm-return list<TVal>
     */
    public function toSortedList(callable $comparator): array
    {
        $list = $this->toList();
        usort($list, $comparator);
        return $list;
    }

    /**
     * @param callable(TVal, TVal): int $comparator
     * @return array<TVal>
     */
    public function toSortedArray(callable $comparator): array
    {
        $list = $this->toArray();
        uasort($list, $comparator);
        return $list;
    }

    /**
     * @return array<value-of<TVal>[]>
     * @psalm-suppress MixedReturnTypeCoercion, MixedAssignment
     */
    public function unzip(): array
    {
        $res = []; $iter = $this->getIter(); $iter->rewind();
        while ($iter->valid()) {
            $i = 0;
            $curr = $iter->current();
            foreach ($curr as $item) {
                $res[$i][] = $item;
                $i++;
            }
            $iter->next();
        }
        return $res;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return iterator_count($this->getIter());
    }

    /**
     * @param int $limit
     * @return int
     */
    public function countUntil(int $limit): int
    {
        $count = 0; $iter = $this->getIter(); $iter->rewind();
        while ($iter->valid() && $count !== $limit) {
            $count++;
            $iter->next();
        }
        return $count;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        $iter = $this->getIter();
        return !$iter->valid();
    }

    /**
     * @param int $n
     * @return Option<TVal>
     */
    public function nth(int $n): Option
    {
        $iter = $this->getIter();
        $value = Option::None();

        $iter->rewind(); $i = -1;
        while (++$i < $n && $iter->valid()) {
            $iter->next();
        }
        if ($iter->valid()) {
            $value = Option::Some($iter->current());
        }

        return $value;
    }

    /**
     * @param ?callable(TVal, TVal): (int|float) $comparator
     * @return Option<TVal>
     */
    public function min(?callable $comparator): Option
    {
        $iter = $this->getIter(); $iter->rewind(); $min = null;
        if ($iter->valid()) {
            $min = $iter->current();
            $iter->next();
        }

        if ($comparator === null) {
            while ($iter->valid()) {
                $curr = $iter->current();
                $min = ($min > $curr) ? $curr : $min;
            }
        } else {
            while ($iter->valid()) {
                $curr = $iter->current();
                $min = ($comparator($min, $curr) > 0) ? $curr : $min;
            }
        }

        return Option::wrap($min);
    }

    /**
     * @param ?callable(TVal, TVal): (int|float) $comparator
     * @return Option<TVal>
     */
    public function max(?callable $comparator): Option
    {
        $iter = $this->getIter(); $iter->rewind(); $max = null;
        if ($iter->valid()) {
            $max = $iter->current();
            $iter->next();
        }

        if ($comparator === null) {
            while ($iter->valid()) {
                $curr = $iter->current();
                $max = ($max < $curr) ? $curr : $max;
            }
        } else {
            while ($iter->valid()) {
                $curr = $iter->current();
                $max = ($comparator($max, $curr) < 0) ? $curr : $max;
            }
        }

        return Option::wrap($max);
    }

    /**
     * @param ?callable(TVal, TVal): (int|float) $comparator
     * @return Option<int<0, max>>
     * @psalm-suppress InvalidReturnType
     */
    public function positionMin(?callable $comparator): Option
    {
        $iter = $this->getIter(); $iter->rewind(); $min = null;
        $i = 0; $iMin = null;
        if ($iter->valid()) {
            $min = $iter->current();
            $iMin = 0;
            $iter->next();
        }

        if ($comparator === null) {
            while ($iter->valid()) {
                $curr = $iter->current(); $i++;
                if ($min > $curr) {
                    $iMin = $i;
                    $min = $curr;
                }
            }
        } else {
            while ($iter->valid()) {
                $curr = $iter->current(); $i++;
                if ($comparator($min, $curr) > 0) {
                    $iMin = $i;
                    $min = $curr;
                }
            }
        }

        return Option::wrap($iMin);
    }

    /**
     * @param ?callable(TVal, TVal): (int|float) $comparator
     * @return Option<int<0, max>>
     * @psalm-suppress InvalidReturnType
     */
    public function positionMax(?callable $comparator): Option
    {
        $iter = $this->getIter(); $iter->rewind(); $max = null;
        $i = 0; $iMax = null;
        if ($iter->valid()) {
            $max = $iter->current();
            $iMax = 0;
            $iter->next();
        }

        if ($comparator === null) {
            while ($iter->valid()) {
                $curr = $iter->current(); $i++;
                if ($max < $curr) {
                    $max = $curr;
                    $iMax = $i;
                }
            }
        } else {
            while ($iter->valid()) {
                $curr = $iter->current(); $i++;
                if ($comparator($max, $curr) < 0) {
                    $max = $curr;
                    $iMax = $i;
                }
            }
        }

        return Option::wrap($iMax);
    }

    /**
     * @param string $separator
     * @return string
     */
    public function join(string $separator = ""): string
    {
        $str = ""; $iter = $this->getIter(); $iter->rewind();
        while ($iter->valid()) {
            /** @psalm-suppress MixedOperand */
            $str .= $separator . $iter->current();
            $iter->next();
        }
        return mb_substr($str, mb_strlen($separator));
    }

    /**
     * @template UVal
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): UVal $f
     * @param string $separator
     * @return string
     */
    public function mapJoin(callable $f, string $separator = ""): string
    {
        $str = ""; $iter = $this->getIter(); $iter->rewind();
        while ($iter->valid()) {
            /** @psalm-suppress MixedOperand */
            $str .= $separator . $f($iter->current(), $iter->key(), $iter);
            $iter->next();
        }
        return mb_substr($str, mb_strlen($separator));
    }

    /**
     * @param callable(TVal): bool $predicate
     * @return Option<TVal>
     */
    public function find(callable $predicate): Option
    {
        $iter = $this->getIter(); $iter->rewind();
        while ($iter->valid()) {
            $curr = $iter->current();
            if ($predicate($curr) === true) return Option::Some($curr);
            $iter->next();
        }
        return Option::None();
    }

    /**
     * @param callable(TVal): bool $predicate
     * @return Option<TVal>
     */
    public function findLast(callable $predicate): Option
    {
        $last = null; $iter = $this->getIter(); $iter->rewind();
        while ($iter->valid()) {
            $curr = $iter->current();
            if ($predicate($curr) === true) $last = $curr;
            $iter->next();
        }
        return Option::wrap($last);
    }

    /**
     * @param callable(TVal): bool $predicate
     * @return Option<TVal>
     */
    public function findKey(callable $predicate): Option
    {
        $iter = $this->getIter(); $iter->rewind();
        while ($iter->valid()) {
            if ($predicate($iter->current()) === true)
                return Option::Some($iter->key());
            $iter->next();
        }
        return Option::None();
    }

    /**
     * @param callable(TVal): bool $predicate
     * @return Option<TVal>
     */
    public function findLastKey(callable $predicate): Option
    {
        $last = null; $iter = $this->getIter(); $iter->rewind();
        while ($iter->valid()) {
            if ($predicate($iter->current()) === true) $last = $iter->key();
            $iter->next();
        }
        return Option::wrap($last);
    }

    /**
     * @param callable(TVal): bool $predicate
     * @return Option<int>
     */
    public function position(callable $predicate): Option
    {
        $iter = $this->getIter(); $iter->rewind(); $i = 0;
        while ($iter->valid()) {
            if ($predicate($iter->current()) === true)
                return Option::Some($i);
            $i++;
            $iter->next();
        }
        return Option::None();
    }

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return bool
     */
    public function any(callable $predicate): bool
    {
        $iter = $this->getIter();
        $iter->rewind();
        while ($iter->valid()) {
            if ($predicate($iter->current(), $iter->key(), $iter) === true) {
                return true;
            }
            $iter->next();
        }
        return false;
    }

    /**
     * @param callable(TVal, TKey, Iterator<TKey, TVal>): bool $predicate
     * @return bool
     */
    public function all(callable $predicate): bool
    {
        $iter = $this->getIter();
        $iter->rewind();
        while ($iter->valid()) {
            if ($predicate($iter->current(), $iter->key(), $iter) === false) {
                return false;
            }
            $iter->next();
        }
        return true;
    }

    /**
     * @return Option<TVal>
     */
    public function first(): Option
    {
        $i = $this->getIter();
        $i->rewind();
        return $i->valid()
            ? Option::Some($i->current())
            : Option::None();
    }

    /**
     * @return Option<TVal>
     */
    public function last(): Option
    {
        $iter = $this->getIter();
        $iter->rewind();
        $item = null;
        while ($iter->valid()) {
            $item = $iter->current();
            $iter->next();
        }
        return Option::wrap($item);
    }

    /**
     * @psalm-suppress all
     * @return Option<int|float|array>
     */
    public function sum(): Option
    {
        $iter = $this->getIter(); $iter->rewind();
        if (!$iter->valid()) return Option::None();

        $acc = $iter->current();
        $iter->next();
        while ($iter->valid()) {
            $acc += $iter->current();
            $iter->next();
        }
        return Option::Some($acc);
    }

    /**
     * @psalm-suppress all
     * @return Option<int|float|array>
     */
    public function product(): Option
    {
        $iter = $this->getIter(); $iter->rewind();
        if (!$iter->valid()) return Option::None();

        $acc = $iter->current();
        $iter->next();
        while ($iter->valid()) {
            $acc *= $iter->current();
            $iter->next();
        }
        return Option::Some($acc);
    }

    /**
     * @psalm-suppress all
     * @return Option<int|float>
     */
    public function average(): Option
    {
        $iter = $this->getIter(); $iter->rewind();
        if (!$iter->valid()) return Option::None();

        $acc = $iter->current();
        $total = 1;
        $iter->next();
        while ($iter->valid()) {
            $acc += $iter->current();
            $total++;
            $iter->next();
        }
        return Option::Some($acc / $total);
    }

    # endregion Consumers

    /**
     * @return Iterator<TKey, TVal>
     */
    abstract public function getIter(): Iterator;
}