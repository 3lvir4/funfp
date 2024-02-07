<?php

namespace Elvir4\FunFp;

use Iterator;
use Throwable;

/**
 * @template Impl of TryFromIterator
 */
interface TryFromIterator
{
    /**
     * @param Iterator $iterator
     * @psalm-return Result<Impl, Throwable>
     */
    public static function tryFromIterator(Iterator $iterator): Result;
}