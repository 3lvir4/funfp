<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Option;

use Elvir4\FunFp\Option;

/**
 * @template T
 * @extends Option<T>
 * @internal
 */
final class Some extends Option
{
    /**
     * @param T $value
     */
    protected function __construct(private $value) {}

    # region Getters

    /**
     * @psalm-mutation-free
     * @inheritDoc
     */
    #[\Override] public function unwrap(): mixed
    {
        return $this->value;
    }

    /**
     * @psalm-mutation-free
     * @inheritDoc
     */
    #[\Override] public function unwrapOr(mixed $default): mixed
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function unwrapOrElse(callable $f): mixed
    {
        return $this->value;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function getNullable(): mixed
    {
        return $this->value;
    }

    # endregion Getters

    # region State Checkers

    /**
     * @inheritDoc
     */
    #[\Override] public function isSome(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function isNone(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function isSomeAnd(callable $predicate): bool
    {
        return $predicate($this->value);
    }

    # endregion State Checkers

    # region Maps

    /**
     * @inheritDoc
     */
    #[\Override] public function map(callable $f): Option
    {
        return new Some($f($this->value));
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function flatMap(callable $f): Option
    {
        return $f($this->value);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function filter(callable $predicate): Option
    {
        return $predicate($this->value) ? $this : Option::None();
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function mapOr(mixed $default, callable $f): mixed
    {
        return $f($this->value);
    }

    /**
     * @inheritDoc
     */
    #[\Override] public function flatten(): Option
    {
        if ($this->value instanceof Option) {
            return $this->value;
        } else {
            return $this;
        }
    }

    # endregion Maps
}