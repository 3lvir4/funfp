<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Contracts;

use Elvir4\FunFp\IterOps;

/**
 * @template TKey
 * @template-covariant TVal
 */
interface ProvidesIterOps
{
    /**
     * @return IterOps<TKey, TVal>
     */
    public function iter(): IterOps;
}