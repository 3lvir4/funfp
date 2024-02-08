<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Helpers;

use ArrayIterator;
use Countable;
use Elvir4\FunFp\Contracts\FromIterator;
use Elvir4\FunFp\Contracts\ProvidesIterOps;
use Elvir4\FunFp\Contracts\TryFromIterator;
use Elvir4\FunFp\Iter;
use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\Option;
use Elvir4\FunFp\Pipe;
use Elvir4\FunFp\Result;
use Iterator;
use IteratorAggregate;
use Override;
use Traversable;

/**
 * Immutable array wrapper.
 * @template TVal
 * @implements IteratorAggregate<TVal>
 * @implements ProvidesIterOps<array-key, TVal>
 * @implements \Elvir4\FunFp\Contracts\FromIterator<Arr>
 * @implements \Elvir4\FunFp\Contracts\TryFromIterator<Arr>
 * @psalm-suppress MixedReturnTypeCoercion, MixedArgumentTypeCoercion, ImpureMethodCall, ImpureFunctionCall
 * @psalm-immutable
 */
class Arr implements IteratorAggregate, ProvidesIterOps, Countable, \JsonSerializable, FromIterator, TryFromIterator
{
    /**
     * @param array<TVal> $array
     */
    protected function __construct(protected readonly array $array) {}

    /**
     * @template UVal
     * @param array<UVal> $array
     * @return Arr<UVal>
     */
    public static function of(array $array): Arr
    {
        return new Arr($array);
    }

    /**
     * @template U
     * @param U $item
     * @param int<0, max> $count
     * @return Arr<U>
     */
    public static function duplicate(mixed $item, int $count): Arr
    {
        return Arr::of(array_fill(0, $count, $item));
    }

    /**
     * @template U
     * @param U $value
     * @return Arr<U>
     */
    public static function wrap(mixed $value): Arr
    {
        return new Arr([$value]);
    }

    /**
     * @inheritDoc
     */
    #[Override] public function iter(): IterOps
    {
        return new Iter(new ArrayIterator($this->array));
    }

    /**
     * @return Arr<TVal>
     */
    public function values(): Arr
    {
        return Arr::of(array_values($this->array));
    }

    /**
     * @return Arr<array-key>
     */
    public function keys(): Arr
    {
        return Arr::of(array_keys($this->array));
    }

    /**
     * @template T of FromArr
     * @param class-string<T> $fqcn
     * @return T
     * @psalm-suppress MixedInferredReturnType, MixedReturnStatement
     */
    public function into(string $fqcn): mixed
    {
        return $fqcn::fromArr($this);
    }

    /**
     * @param callable ...$funs
     * @return mixed
     */
    public function pipeThrough(callable ...$funs): mixed
    {
        return (new Pipe($funs))->eval($this->array);
    }

    /**
     * @template UVal
     * @param callable(TVal): UVal $f
     * @return Arr<UVal>
     */
    public function map(callable $f): Arr
    {
        return Arr::of(array_map($f, $this->array));
    }

    /**
     * @param array|Arr ...$arrays
     * @return Arr<array>
     */
    public function zip(array|Arr ...$arrays): Arr
    {
        $arrays =  array_map(function($arr) {
            if ($arr instanceof Arr) {
                return $arr->array;
            }
            return $arr;
        }, $arrays);

        return Arr::of(array_map(null, ...$arrays));
    }

    /**
     * @param callable(TVal, array-key): bool $predicate
     * @return Arr<TVal>
     */
    public function filter(callable $predicate): Arr
    {
        return Arr::of(array_filter($this->array, $predicate, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * @param array-key $key
     * @return Option<TVal>
     */
    public function at(mixed $key): Option
    {
        return array_key_exists($key, $this->array)
            ? Option::Some($this->array[$key])
            : Option::None();
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this->array) === 0;
    }

    /**
     * @return bool
     */
    public function isList(): bool
    {
        return array_is_list($this->array);
    }

    /**
     * @param TVal $value
     * @return bool
     */
    public function contains(mixed $value): bool
    {
        if ($this->isEmpty()) return false;
        return in_array($value, $this->array, true);
    }

    /**
     * @return TVal[]
     */
    public function unwrap(): array
    {
        return $this->array;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function getIterator(): Traversable
    {
        return new ArrayIterator($this->array);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function count(): int
    {
        return count($this->array);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function jsonSerialize(): mixed
    {
        return $this->array;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public static function fromIterator(Iterator $iterator): Arr
    {
        return Arr::of(iterator_to_array($iterator));
    }

    /**
     * @inheritDoc
     */
    #[\Override] public static function tryFromIterator(Iterator $iterator): Result
    {
        return Result::try(static fn() => Arr::of(iterator_to_array($iterator)));
    }
}