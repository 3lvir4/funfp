<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Tests;

use Elvir4\FunFp\Pair;
use PHPUnit\Framework\TestCase;

class PairTest extends TestCase
{
    public function test_basics(): void
    {
        $p = new Pair(4, 7);
        [$a, $b] = $p;
        $this->assertEquals(4, $a);
        $this->assertEquals(7, $b);
    }

    public function test_to_string(): void
    {
        $this->assertEquals("(7,5)", (string) new Pair(7, 5));
    }
}
