<?php

declare(strict_types=1);

namespace Elvir4\FunFp;

/**
 * @template TKey
 * @template TVal
 */
interface ProvidesIter
{
    /**
     * @return Iter<TKey, TVal>
     */
    public function iter(): Iter;
}