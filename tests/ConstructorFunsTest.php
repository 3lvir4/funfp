<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Tests;

use Elvir4\FunFp\Iter;
use Elvir4\FunFp\Option;
use Elvir4\FunFp\Result;
use PHPUnit\Framework\TestCase;
use function Elvir4\FunFp\constructors\pipe;
use function Elvir4\FunFp\constructors\Err;
use function Elvir4\FunFp\constructors\generate;
use function Elvir4\FunFp\constructors\iter;
use function Elvir4\FunFp\constructors\None;
use function Elvir4\FunFp\constructors\Ok;
use function Elvir4\FunFp\constructors\Some;

class ConstructorFunsTest extends TestCase
{
    public function test_pipe(): void
    {
        $min = fn(int ...$nums): int => min($nums);
        $mulBy4 = fn(int $n): int => 4 * $n;

        $this->assertEquals(
            2,
            pipe($min, $mulBy4, fn($n) => intdiv($n, 5))(7, 3, 9)
        );

        $piped = pipe($min, $mulBy4, fn($n) => $n + 7);
        $piped
            ->then(fn($n) => intdiv($n, 5))
            ->then(fn($n) => $n % 2 === 0);

        $this->assertFalse($piped(7, 3, 9, 15));
    }

    public function test_iter(): void
    {
        $iterResult = iter([1, 2, 3, 4]);
        $this->assertEquals(
            Result::Ok(new Iter(new \ArrayIterator([1, 2, 3, 4]))),
            $iterResult
        );
    }

    public function test_generate(): void
    {
        $this->assertEquals(
            [0, 1, 2, 3, 4, 5, 6, 7, 8],
            generate(0, fn($n) => $n + 1)->take(9)->toList()
        );
    }

    public function test_constructors(): void
    {
        $this->assertEquals(Result::Ok("value"), Ok("value"));
        $this->assertEquals(Result::Err("error"), Err("error"));
        $this->assertEquals(Option::Some("thing"), Some("thing"));
        $this->assertEquals(Option::None(), None());
    }
}
