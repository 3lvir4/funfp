<?php

declare(strict_types=1);

namespace Elvir4\FunFp;

use Iterator;

/**
 * @template-covariant Impl
 */
interface FromIterator
{
    /**
     * @param Iterator $iterator
     * @return Impl
     */
    public static function fromIterator(Iterator $iterator): self;
}