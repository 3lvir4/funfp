<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Helpers;

use Elvir4\FunFp\FromIterator;
use Elvir4\FunFp\Helpers\String\AsciiStringIterator;
use Elvir4\FunFp\Helpers\String\BytesStringIterator;
use Elvir4\FunFp\Helpers\String\Utf8CodepointsStringIterator;
use Elvir4\FunFp\Helpers\String\Utf8StringIterator;
use Elvir4\FunFp\Iter;
use Elvir4\FunFp\Result;
use Elvir4\FunFp\TryFromIterator;
use Iterator;
use IteratorAggregate;
use Stringable;
use Traversable;

/**
 * Immutable UTF-8 string wrapper.
 * @psalm-immutable
 * @implements IteratorAggregate<int, string>
 * @implements TryFromIterator<Str>
 * @implements FromIterator<Str>
 * @psalm-suppress ImpureMethodCall, MixedArgumentTypeCoercion
 */
class Str implements Stringable, IteratorAggregate, FromIterator, TryFromIterator
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
     * @param iterable<string> $chars
     * @return Str
     * @psalm-suppress InvalidArgument
     */
    public static function fromChars(iterable $chars): Str
    {
        return Str::of(implode(iterator_to_array($chars, false)));
    }

    public function unwrap(): string
    {
        return $this->str;
    }

    /**
     * @return Iter<int, string>
     */
    public function chars(): Iter
    {
        return new Iter(new Utf8StringIterator($this->str));
    }

    /**
     * @return Iter<int, int>
     */
    public function bytes(): Iter
    {
        return new Iter(new BytesStringIterator($this->str));
    }

    public function codepoints(): Iter
    {
        return new Iter(new Utf8CodepointsStringIterator($this->str));
    }

    /**
     * @return Iter<int, string>
     */
    public function byteChars(): Iter
    {
        return new Iter(new AsciiStringIterator($this->str));
    }

    public function byteCount(): int
    {
        return strlen($this->str);
    }

    public function length(): int
    {
        return mb_strlen($this->str, "UTF-8");
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
    #[\Override] public function getIterator(): Traversable
    {
        return $this->chars();
    }

    #[\Override] public static function fromIterator(Iterator $iterator): self
    {
        return Str::of(
            implode("", iterator_to_array($iterator, false))
        );
    }

    /**
     * @inheritDoc
     */
    #[\Override] public static function tryFromIterator(Iterator $iterator): Result
    {
        return Result::try(
            static fn() => Str::of(
                implode("", iterator_to_array($iterator, false))
            )
        );
    }
}