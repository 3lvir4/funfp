<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Iter;

use Elvir4\FunFp\IterTrait;
use Iterator;
use MultipleIterator;

/**
 * @extends MultipleIterator<mixed, mixed>
 * @psalm-suppress InvalidArgument, InvalidReturnType
 */
class ZipMultipleIter extends MultipleIterator
{
    /**
     * @use IterTrait<array, array>
     */
    use IterTrait;

    public function __construct(Iterator ...$iterators)
    {
        parent::__construct();
        foreach ($iterators as $iterator) {
            $this->attachIterator($iterator);
        }
    }

    /**
     * @inheritDoc
     * @psalm-return Iterator<array, array>
     */
    protected function getIter(): Iterator
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function join(string $separator = ""): never
    {
        throw new \RuntimeException("Attempt to call join on iterator of iterators.");
    }
}