<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Option;

use Elvir4\FunFp\Option;

/**
 * @extends Option<mixed>
 * @internal
 */
final class None extends Option
{
    protected static ?Option $instance = null;
    protected function __construct() {}

    /**
     * @return Option<mixed>
     */
    protected static function getInstance(): Option
    {
        return self::$instance === null
            ? self::$instance = new None()
            : self::$instance;
    }

    # region Getters

    /**
     * @psalm-mutation-free
     * @inheritDoc
     */
    #[\Override] public function unwrap(): mixed
    {
        throw new \RuntimeException("Called `Option::unwrap()` on `None` variant.");
    }

    /**
     * @psalm-mutation-free
     * @inheritDoc
     */
    #[\Override] public function unwrapOr(mixed $default): mixed
    {
        return $default;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function unwrapOrElse(callable $f): mixed
    {
        return $f();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function getNullable(): null
    {
        return null;
    }

    # endregion Getters

    # region State Checkers

    /**
     * @inheritDoc
     */
    #[\Override] public function isSome(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function isNone(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function isSomeAnd(callable $predicate): bool
    {
        return false;
    }

    # endregion State Checkers

    # region Maps

    /**
     * @inheritDoc
     */
    #[\Override] public function map(callable $f): Option
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function flatMap(callable $f): Option
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function filter(callable $predicate): Option
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

    /**
     * @inheritDoc
     */
    #[\Override] public function flatten(): Option
    {
        return $this;
    }

    # endregion Maps
}