<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use function Elvir4\FunFp\constructors\generate;
use function Elvir4\FunFp\constructors\pipe;
use function Elvir4\FunFp\Helpers\fold;
use function Elvir4\FunFp\Helpers\op;

class MiscTest extends TestCase
{
    public function test_op(): void
    {
        $res = generate(0, op("+", 1))
            ->filter(fn($n) => $n % 2 === 0)
            ->map(op("*", 2))
            ->take(5)
            ->sum();

        $this->assertEquals(40, $res->unwrap());

        $res = fold([1, 2, 3, 4, 5], 1, op('*'));
        $this->assertEquals(120, $res);
    }
}
