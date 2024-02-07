<?php

namespace Elvir4\FunFp\Tests;

use Elvir4\FunFp\Option;
use Elvir4\FunFp\Result;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    public function test_try_construct(): void
    {
        $intDivThrows = function(int $n, int $d): int|float {
            if ($d === 0) throw new \InvalidArgumentException("Divisor can't be 0.");
            return $n / $d;
        };

        $this->assertEquals(
            Result::Ok(5),
            Result::try(fn() => $intDivThrows(10, 2))
        );
        $this->assertEquals(
            Result::Err(new \InvalidArgumentException("Divisor can't be 0.")),
            Result::try(fn() => $intDivThrows(10, 0))
        );
    }

    # region Getters Test

    public function test_unwrap(): void
    {
        $this->assertEquals(10, Result::Ok(10)->unwrap());
        $this->expectException(\RuntimeException::class);
        Result::Err("Error message.")->unwrap();
        $this->expectException(\RangeException::class);
        $this->expectExceptionMessage("Actual error.");
        Result::Err(new \RangeException("Actual error."))->unwrap();
    }

    public function test_unwrap_or(): void
    {
        $this->assertEquals("ok", Result::Ok("ok")->unwrapOr("default"));
        $this->assertEquals("default", Result::Err("not ok")->unwrapOr("default"));
    }

    public function test_unwrap_err(): void
    {
        $this->assertEquals("error", Result::Err("error")->unwrapErr());
        $this->expectException(\RuntimeException::class);
        Result::Ok("val")->unwrapErr();
    }

    public function test_unwrap_or_else(): void
    {
        $this->assertEquals(
            "ok",
            Result::Ok("ok")->unwrapOrElse(fn() => "this is fine.")
        );
        $this->assertEquals(
            "this is fine.",
            Result::Err("error")->unwrapOrElse(fn() => "this is fine.")
        );
    }

    public function test_ok_getter(): void
    {
        $this->assertEquals(
            Option::Some("thing"),
            Result::Ok("thing")->get()
        );

        $this->assertEquals(
            Option::None(),
            Result::Err("error")->get()
        );
    }

    # endregion Getters Test

    public function test_state_checkers(): void
    {
        $ok2 = Result::Ok(2);
        $ok3 = Result::Ok(3);
        $thisIsFine = Result::Err("this is fine.");
        $notFine = Result::Err("not fine.");

        $this->assertTrue($ok2->isOk());
        $this->assertTrue($ok2->isOkAnd(fn($n) => $n % 2 === 0));
        $this->assertFalse($ok3->isOkAnd(fn($n) => $n % 2 === 0));
        $this->assertFalse($notFine->isOk());
        $this->assertFalse($notFine->isOkAnd(fn($n) => $n % 2 === 0));

        $this->assertTrue($thisIsFine->isErr());
        $this->assertFalse($ok2->isErr());
        $this->assertTrue($thisIsFine->isErrAnd(fn($msg) => $msg === "this is fine."));
        $this->assertFalse($notFine->isErrAnd(fn($msg) => $msg === "this is fine."));
        $this->assertFalse($ok2->isErrAnd(fn($msg) => $msg === "this is fine."));
    }

    # region Maps Test

    public function test_map(): void
    {
        $this->assertEquals(
            Result::Ok(50),
            Result::Ok(10)->map(fn($n) => $n * 5)
        );

        $this->assertEquals(
            Result::Err("nope"),
            Result::Err("nope")->map(fn($n) => $n * 5)
        );
    }

    public function test_map_err(): void
    {
        $this->assertEquals(
            Result::Ok(10),
            Result::Ok(10)->mapErr(fn($msg) => str_split($msg))
        );

        $this->assertEquals(
            Result::Err(["n", "o", "p", "e"]),
            Result::Err("nope")->mapErr(fn($msg) => str_split($msg))
        );
    }

    public function test_flat_map(): void
    {
        $intDivBy2 = function (int $n): Result {
            if ($n % 2 === 1) return Result::Err("odd");
            return Result::Ok($n / 2);
        };

        $this->assertEquals(
            Result::Ok(5),
            Result::Ok(10)->flatMap($intDivBy2)
        );

        $this->assertEquals(
            Result::Err("odd"),
            Result::Ok(11)->flatMap($intDivBy2)
        );

        $this->assertEquals(
            Result::Err("error"),
            Result::Err("error")->flatMap($intDivBy2)
        );
    }

    public function test_map_or(): void
    {
        $this->assertEquals(
            5,
            Result::Ok(2)
                ->mapOr(0, fn($n) => $n + 3)
        );

        $this->assertEquals(
            0,
            Result::Err("nope")
                ->mapOr(0, fn($n) => $n + 3)
        );
    }

    # endregion Maps Test
}
