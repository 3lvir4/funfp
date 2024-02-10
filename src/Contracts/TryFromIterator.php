<?php

namespace Elvir4\FunFp\Contracts;

use Elvir4\FunFp\Result;
use Iterator;
use Throwable;

/**
 * @template-covariant Impl
 */
interface TryFromIterator
{
    /**
     * @param Iterator $iterator
     * @psalm-return Result<Impl, Throwable>
     */
    public static function tryFromIterator(Iterator $iterator): Result;
}