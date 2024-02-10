<?php

declare(strict_types=1);

namespace Elvir4\FunFp;

use Elvir4\FunFp\Contracts\ProvidesIterOps;
use function Elvir4\FunFp\constructors\pipe;

/**
 * Represents a chain of operations.
 *
 * @see pipe()
 * @link https://en.wikipedia.org/wiki/Pipeline_(software)
 * @psalm-suppress MixedAssignment
 * @implements ProvidesIterOps<int, callable>
 */
final class Pipe implements ProvidesIterOps
{
    /**
     * @param callable[] $fns
     */
    public function __construct(
        private array $fns
    ) {}

    /**
     * @see pipe()
     * @param mixed ...$args
     * @return mixed
     */
    public function __invoke(mixed ...$args): mixed
    {
        return $this->eval(...$args);
    }

    /**
     * Adds a callable to the pipeline.
     *
     * @param callable $f
     * @return $this
     */
    public function then(callable $f): self
    {
        $this->fns[] = $f;
        return $this;
    }

    /**
     * Evaluates the pipeline with the provided arguments as initial input.
     *
     * @param mixed ...$args
     * @return mixed
     */
    public function eval(mixed ...$args): mixed
    {
        $output = call_user_func($this->fns[0], ...$args);
        $i = 0;
        while (++$i < count($this->fns)) {
            $output = call_user_func($this->fns[$i], $output);
        }
        return $output;
    }

    /**
     * @inheritDoc
     * @psalm-suppress MixedReturnTypeCoercion
     */
    #[\Override] public function iter(): IterOps
    {
        return new Iter(new \ArrayIterator($this->fns));
    }
}