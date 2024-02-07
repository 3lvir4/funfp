<?php

declare(strict_types=1);

namespace Elvir4\FunFp;

/**
 * @template TKey
 * @template TVal
 */
interface ProvidesIterOps
{
    /**
     * @return IterOps<TKey, TVal>
     */
    public function iter(): IterOps;
}