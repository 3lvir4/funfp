<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Result;

use Elvir4\FunFp\Option;
use Elvir4\FunFp\Result;

/**
 * @template E
 * @extends Result<mixed, E>
 * @internal
 */
final class Err extends Result
{
    /**
     * @param E $error
     */
    public function __construct(private $error) {}

    # region Getters

    /**
     * @inheritDoc
     */
    #[\Override] public function unwrap(): never
    {
        if ($this->error instanceof \Throwable) throw $this->error;
        throw new \RuntimeException("Called `Result::unwrap()` on Err variant.");
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function unwrapOr(mixed $default): mixed
    {
        return $default;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function unwrapErr(): mixed
    {
        return $this->error;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function unwrapOrElse(callable $fallback): mixed
    {
        return $fallback();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function get(): Option
    {
        return Option::None();
    }

    # endregion Getters

    # region State Checkers

    /**
     * @inheritDoc
     */
    #[\Override] public function isOk(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function isOkAnd(callable $predicate): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function isErr(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function isErrAnd(callable $predicate): bool
    {
        return $predicate($this->error);
    }

    # endregion State Checkers

    # region Maps

    /**
     * @inheritDoc
     */
    #[\Override] public function map(callable $f): Result
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function mapErr(callable $f): Result
    {
        return Result::Err($f($this->error));
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function flatMap(callable $f): Result
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function mapOr(mixed $default, callable $f): mixed
    {
        return $default;
    }

    # endregion Maps

}