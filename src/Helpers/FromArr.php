<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Helpers;

/**
 * @template Impl
 */
interface FromArr
{
    /**
     * @param Arr $arr
     * @return Impl
     */
    public static function fromArr(Arr $arr): mixed;
}