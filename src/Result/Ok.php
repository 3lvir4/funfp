<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Result;

use Elvir4\FunFp\Option;
use Elvir4\FunFp\Result;

/**
 * @template T
 * @extends Result<T, mixed>
 * @internal
 */
final class Ok extends Result
{
    /**
     * @param T $value
     */
    protected function __construct(private $value) {}

    # region Getters

    /**
     * @inheritDoc
     */
    #[\Override] public function unwrap(): mixed
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function unwrapOr(mixed $default): mixed
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function unwrapErr(): mixed
    {
        throw new \RuntimeException("Called `Result::unwrapErr()` on Ok variant.");
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function unwrapOrElse(callable $fallback): mixed
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function get(): Option
    {
        return Option::Some($this->value);
    }

    # endregion Getters

    # region State Checkers

    /**
     * @inheritDoc
     */
    #[\Override] public function isOk(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function isOkAnd(callable $predicate): bool
    {
        return $predicate($this->value);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function isErr(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function isErrAnd(callable $predicate): bool
    {
        return false;
    }

    # endregion State Checkers

    # region Maps

    /**
     * @inheritDoc
     */
    #[\Override] public function map(callable $f): Result
    {
        return Result::Ok($f($this->value));
    }


    /**
     * @inheritDoc
     */
    #[\Override] public function mapErr(callable $f): Result
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function flatMap(callable $f): Result
    {
        return $f($this->value);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function mapOr(mixed $default, callable $f): mixed
    {
        return $f($this->value);
    }

    # endregion Maps
}