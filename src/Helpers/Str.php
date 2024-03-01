<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Helpers;

use Countable;
use Elvir4\FunFp\Contracts\FromIterator;
use Elvir4\FunFp\Contracts\TryFromIterator;
use Elvir4\FunFp\Helpers\String\AsciiStringIterator;
use Elvir4\FunFp\Helpers\String\BytesIterator;
use Elvir4\FunFp\Helpers\String\Utf8CharsIterator;
use Elvir4\FunFp\Helpers\String\Utf8CodepointsIterator;
use Elvir4\FunFp\Helpers\String\Utf8LinesIterator;
use Elvir4\FunFp\Helpers\String\Utf8WordsIterator;
use Elvir4\FunFp\Iter;
use Elvir4\FunFp\IterOps;
use Elvir4\FunFp\Option;
use Elvir4\FunFp\Result;
use Iterator;
use IteratorAggregate;
use JsonSerializable;
use Override;
use Stringable;
use Traversable;

/**
 * Immutable UTF-8 string wrapper.
 *
 * @psalm-immutable
 * @implements IteratorAggregate<int, string>
 * @implements TryFromIterator<Str>
 * @implements FromIterator<Str>
 * @implements FromArr<Str>
 * @psalm-suppress ImpureMethodCall, MixedArgumentTypeCoercion, ImpureFunctionCall
 */
class Str implements Stringable, IteratorAggregate, FromIterator, TryFromIterator, Countable, JsonSerializable, FromArr
{
    /**
     * @param string $str
     */
    protected function __construct(
        protected readonly string $str
    ) {}

    /**
     * @param string $str
     * @return Str
     */
    public static function of(string $str): Str
    {
        return new Str($str);
    }

    /**
     * @param Str|string $first
     * @param Str|string ...$strings
     * @return Str
     */
    public static function concat(Str|string $first, Str|string ...$strings): Str
    {
        $buf = (string) $first;
        while (count($strings) > 0) {
            $buf .= array_pop($strings);
        }
        return Str::of($buf);
    }

    /**
     * @param iterable<string> $iter
     * @return Str
     */
    public static function fromIterable(iterable $iter): Str
    {
        if (is_array($iter)) return Str::of(implode($iter));
        return Str::of(implode(iterator_to_array($iter, false)));
    }

    /**
     * @param string $str
     * @param int $times
     * @return Str
     */
    public static function repeat(string $str, int $times): Str
    {
        return Str::of(str_repeat($str, $times));
    }

    /**
     * @param string $separator
     * @return Arr<string>
     */
    public function explode(string $separator = ""): Arr
    {
        return $separator === ""
            ? Arr::of(mb_str_split($this->str, encoding: "UTF-8"))
            : Arr::of(explode($separator, $this->str));
    }

    public function toUpper(): Str
    {
        return Str::of(mb_strtoupper($this->str, "UTF-8"));
    }

    public function toLower(): Str
    {
        return Str::of(mb_strtolower($this->str, "UTF-8"));
    }

    public function trim(): Str
    {
        return Str::of(trim($this->str));
    }

    public function trimLeft(): Str
    {
        return Str::of(ltrim($this->str));
    }

    public function trimRight(): Str
    {
        return Str::of(rtrim($this->str));
    }

    /**
     * @param int $length
     * @param string $padStr
     * @return Str
     */
    public function pad(int $length, string $padStr = ' '): Str
    {
        return $this->_pad($length, $padStr, STR_PAD_BOTH);
    }

    /**
     * @param int $length
     * @param string $padStr
     * @return Str
     */
    public function padLeft(int $length, string $padStr = ' '): Str
    {
        return $this->_pad($length, $padStr, STR_PAD_LEFT);
    }

    /**
     * @param int $length
     * @param string $padStr
     * @return Str
     */
    public function padRight(int $length, string $padStr = ' '): Str
    {
        return $this->_pad($length, $padStr, STR_PAD_RIGHT);
    }

    /**
     * @param int $times
     * @return Str
     */
    public function repeated(int $times): Str
    {
        return Str::repeat($this->str, $times);
    }

    /**
     * @return bool
     * @psalm-assert-if-true non-empty-string $this->str
     */
    public function isEmpty(): bool
    {
        return $this->str === "";
    }

    /**
     * @param Str|string|iterable<string> $toAppend
     * @return Str
     */
    public function append(Str|string|iterable $toAppend): Str
    {
        if (is_iterable($toAppend)) {
            return is_array($toAppend)
                ? Str::of($this->str . implode($toAppend))
                : Str::of($this->str . implode(iterator_to_array($toAppend, false)));
        }

        return Str::of($this->str . $toAppend);
    }

    /**
     * @param Str|string|iterable<string> $toPrepend
     * @return Str
     */
    public function prepend(Str|string|iterable $toPrepend): Str
    {
        if (is_iterable($toPrepend)) {
            return is_array($toPrepend)
                ? Str::of( implode($toPrepend) . $this->str )
                : Str::of(implode(iterator_to_array($toPrepend, false)) . $this->str );
        }

        return Str::of($toPrepend . $this->str);
    }

    /**
     * @param int $length
     * @param string $padStr
     * @param int<0, 2> $opt
     * @return Str
     */
    protected function _pad(int $length, string $padStr, int $opt): Str
    {
        return Str::of(mb_str_pad($this->str, $length, $padStr, $opt));
    }

    public function unwrap(): string
    {
        return $this->str;
    }

    /**
     * @return IterOps<int, string>&Traversable<int, string>
     */
    public function chars(): IterOps&Traversable
    {
        return new Iter(new Utf8CharsIterator($this->str));
    }

    /**
     * @return IterOps<int, string>&Traversable<int, string>
     */
    public function words(): IterOps&Traversable
    {
        return new Iter(new Utf8WordsIterator($this->str));
    }

    /**
     * @return IterOps<int, string>&Traversable<int, string>
     */
    public function lines(): IterOps&Traversable
    {
        return new Iter(new Utf8LinesIterator($this->str));
    }

    /**
     * @return IterOps<int, int>&Traversable<int, int>
     */
    public function bytes(): IterOps&Traversable
    {
        return new Iter(new BytesIterator($this->str));
    }

    /**
     * @return IterOps<int, int>&Traversable<int, int>
     */
    public function codepoints(): IterOps&Traversable
    {
        return new Iter(new Utf8CodepointsIterator($this->str));
    }

    /**
     * @return IterOps<int, string>&Traversable<int, string>
     */
    public function byteChars(): IterOps&Traversable
    {
        return new Iter(new AsciiStringIterator($this->str));
    }

    public function byteCount(): int
    {
        return strlen($this->str);
    }

    /**
     * @param callable(string): string $f
     * @return Str
     */
    public function map(callable $f): Str
    {
        return Str::of($f($this->str));
    }

    public function length(): int
    {
        return mb_strlen($this->str, "UTF-8");
    }

    /**
     * @param int $index
     * @return Option<string>
     */
    public function charAt(int $index): Option
    {
        $len = mb_strlen($this->str);
        if ($index > 0 ? $index >= $len : $index < -$len)
            return Option::None();
        return Option::Some(mb_substr($this->str, $index, 1));
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->str;
    }

    /**
     * @inheritDoc
     */
    #[Override] public function getIterator(): Traversable
    {
        return $this->chars();
    }

    #[Override] public static function fromIterator(Iterator $iterator): self
    {
        return Str::of(
            implode("", iterator_to_array($iterator, false))
        );
    }

    /**
     * @inheritDoc
     */
    #[Override] public static function tryFromIterator(Iterator $iterator): Result
    {
        return Result::try(
            static fn() => Str::of(
                implode("", iterator_to_array($iterator, false))
            )
        );
    }

    /**
     * @inheritDoc
     */
    #[Override] public function count(): int
    {
        return $this->length();
    }

    /**
     * @inheritDoc
     */
    #[Override] public function jsonSerialize(): string
    {
        return $this->str;
    }

    /**
     * @inheritDoc
     */
    #[Override] public static function fromArr(Arr $arr): Str
    {
        return Str::of(join($arr->unwrap()));
    }
}