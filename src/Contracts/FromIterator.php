<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Contracts;

use Iterator;

/**
 * @template-covariant Impl
 */
interface FromIterator
{
    /**
     * @param Iterator $iterator
     * @return FromIterator<Impl>
     */
    public static function fromIterator(Iterator $iterator): mixed;
}