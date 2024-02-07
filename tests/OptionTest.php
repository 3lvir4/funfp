<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Tests;

use Elvir4\FunFp\Option;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
    public function test_construct_from_value(): void
    {
        $this->assertEquals(
            Option::Some("test"),
            Option::wrap("test")
        );
        $this->assertEquals(
            Option::None(),
            Option::wrap(null)
        );
        $this->assertEquals(
            Option::None(),
            Option::wrap(0, 0)
        );
    }

    public function test_construct_from_callable_return_value(): void
    {
        $this->assertEquals(
            Option::Some(55),
            Option::wrapProcedure(function () {
                $i = 0; $a = 0;
                while (++$i <= 10) {
                    $a += $i;
                }
                return $a;
            })
        );

        $this->assertEquals(
            Option::None(),
            Option::wrapProcedure(fn() => null)
        );
    }

    public function test_with_expr_construct(): void
    {
        $o = Option::with(function () {
            $a = yield Option::Some("hello");
            $b = yield Option::Some("world");
            return ucfirst($a) . " " . $b . "!";
        });

        $this->assertEquals(
            Option::Some("Hello world!"),
            $o
        );

        $o = Option::with(function () {
            $a = yield Option::Some(1);
            $b = yield Option::Some(2);
            $c = yield Option::None();
            $d = yield Option::Some(4);
            return $a + $b + $c + $d;
        });

        $this->assertEquals(
            Option::None(),
            $o
        );
    }

    # region Getters Test

    public function test_unwrap(): void
    {
        $this->assertEquals("thing", Option::Some("thing")->unwrap());
        $this->expectException(\RuntimeException::class);
        Option::None()->unwrap();
    }

    public function test_unwrap_or(): void
    {
        $this->assertEquals("thing", Option::Some("thing")->unwrapOr("default"));
        $this->assertEquals("default", Option::None()->unwrapOr("default"));
    }

    public function test_unwrap_or_else(): void
    {
        $this->assertEquals(
            "thing",
            Option::Some("thing")->unwrapOrElse(fn() => "default")
        );
        $this->assertEquals(
            "default",
            Option::None()->unwrapOrElse(fn() => "default")
        );
    }

    # endregion Getters Test

    public function test_state_checkers(): void
    {
        $some2 = Option::Some(2);
        $some3 = Option::Some(3);
        $none = Option::None();
        $this->assertTrue($some2->isSome());
        $this->assertTrue($none->isNone());
        $this->assertFalse($some2->isNone());
        $this->assertFalse($none->isSome());
        $this->assertTrue($some2->isSomeAnd(fn($n) => $n % 2 === 0));
        $this->assertFalse($none->isSomeAnd(fn($n) => $n % 2 === 0));
        $this->assertFalse($some3->isSomeAnd(fn($n) => $n % 2 === 0));
    }

    # region Maps Test

    public function test_map(): void
    {
        $this->assertEquals(
            Option::Some(25),
            Option::Some(2)
                ->map(fn($n) => $n + 3)
                ->map(fn($n) => $n ** 2)
        );

        $this->assertEquals(
            Option::None(),
            Option::None()
                ->map(fn($n) => $n + 3)
                ->map(fn($n) => $n ** 2)
        );
    }

    public function test_flat_map(): void
    {
        $intdiv = function(int $n, int $d): Option {
            if ($d === 0) return Option::None();
            return Option::Some($n / $d);
        };

        $this->assertEquals(
            Option::Some(23),
            Option::Some(69)
                ->flatMap(fn($n) => $intdiv($n, 3))
        );

        $this->assertEquals(
            Option::None(),
            Option::Some(69)
                ->flatMap(fn($n) => $intdiv($n, 0))
        );

        $this->assertEquals(
            Option::None(),
            Option::None()
                ->flatMap(fn($n) => $intdiv($n, 3))
        );
    }

    public function test_filter(): void
    {
        $this->assertEquals(
            Option::Some(17),
            Option::Some(17)->filter(fn($n) => $n % 2 === 1)
        );

        $this->assertEquals(
            Option::None(),
            Option::Some(16)->filter(fn($n) => $n % 2 === 1)
        );

        $this->assertEquals(
            Option::None(),
            Option::None()->filter(fn($n) => $n % 2 === 1)
        );
    }

    public function test_map_or(): void
    {
        $this->assertEquals(
            5,
            Option::Some(2)
                ->mapOr(0, fn($n) => $n + 3)
        );

        $this->assertEquals(
            0,
            Option::None()
                ->mapOr(0, fn($n) => $n + 3)
        );
    }

    # endregion Maps Test
}

