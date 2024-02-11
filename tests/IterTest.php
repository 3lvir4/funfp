<?php

namespace Elvir4\FunFp\Tests;

use Elvir4\FunFp\Helpers\Arr;
use Elvir4\FunFp\Iter\RewindableIter;
use Elvir4\FunFp\Option;
use PHPUnit\Framework\TestCase;
use function Elvir4\FunFp\constructors\generate;
use function Elvir4\FunFp\constructors\iter;

class IterTest extends TestCase
{
    public function test_flat_map(): void
    {
        $iter = iter([1, 4, 7])->unwrap();
        $this->assertEquals(
            [1, 2, 3, 4, 5, 6, 7, 8, 9],
            $iter->flatMap(fn($n) => [$n, $n +1, $n + 2])->toList()
        );

        $this->assertEquals(
            [7, 8, 9],
            $iter->flatMap(fn($n) => [$n, $n +1, $n + 2])->toArray()
        );
    }

    public function test_nth(): void
    {
        $arr = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

        $iter = iter($arr)->unwrap()->filter(fn($n) => $n % 2 === 0);

        var_dump($iter);

        $this->assertEquals(Option::Some(8), $iter->nth(4));
        $this->assertEquals(Option::None(), $iter->nth(6));
        $this->assertEquals(Option::Some(2), $iter->nth(1));
    }

    public function test_to_array(): void
    {
        $arr = [
            "foo" => 13,
            "bar" => 69,
            "baz" => 420,
            "fizz" => -33,
            "buzz" => 18
        ];

        $res = iter($arr)->unwrap()->filter(fn($n) => $n % 2 === 0);
        $this->assertEquals(["baz" => 420, "buzz" => 18], $res->toArray());
    }

    public function test_skip_while(): void
    {
        $arr = [1, 3, 5, 7, 9, 10, 12, 14];
        $this->assertEquals(
            [10, 12, 14],
            iter($arr)->unwrap()->skipWhile(fn($n) => $n % 2 !== 0)->toList()
        );
    }

    public function test_chunk_by(): void
    {
        $i = iter([1, 2, 7, 3, 3, 4, 5, 9, 11, 6, 8, 13, 13])->unwrap();
        $this->assertEquals(
            [[1], [2], [7, 3, 3], [4], [5, 9, 11], [6, 8], [13, 13]],
            $i->chunkBy(fn($n) => $n % 2 === 1)->toList()
        );
    }

    public function test_slice(): void
    {
        $i = iter([1, 2, 3, 4, 5, 6])->unwrap();
        $this->assertEquals([2, 3, 4], $i->slice(1, 3)->toList());
    }

    public function test_take_every(): void
    {
        $arr = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $this->assertEquals(
            [1, 3, 5, 7, 9],
            iter($arr)->unwrap()->takeEvery(2)->toList()
        );

        $this->assertEquals(["hi"], iter(["hi"])->unwrap()->takeEvery(10)->toList());
        $this->assertEquals([], iter([1, 2, 3])->unwrap()->takeEvery(0)->toList());
    }

    public function test_skip_every(): void
    {
        $arr = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $this->assertEquals(
            [2, 3, 4, 6, 7, 8, 10],
            iter($arr)->unwrap()->skipEvery(4)->toList()
        );

        $this->assertEquals($arr, iter($arr)->unwrap()->skipEvery(0)->toList());
        $this->assertEquals([], iter($arr)->unwrap()->skipEvery(1)->toList());
    }

    public function test_to_sorted_list(): void
    {
        $arr = [12, 3, 7, 19, 15, 21, 1, -12, 33];
        $this->assertEquals(
            [-12, 1, 3, 7, 12, 15, 19, 21, 33],
            iter($arr)->unwrap()->toSortedList(fn($a, $b) => $a - $b)
        );
    }

    public function test_sorted(): void
    {
        $iter = iter([2, 7, 1, 3, 4, -5, 9, 11, -2, 6, 10])->unwrap()->take(10);
        $this->assertEquals(
            [-10, -4, 2, 4, 6, 8, 12, 14, 18, 22],
            $iter->sorted(fn($a, $b) => $a - $b)->map(fn($n) => $n * 2)->toList()
        );
    }

    public function test_enumerate(): void
    {
        $i = iter(["foo", "bar", "baz"])->unwrap();
        $this->assertEquals(
            [[0, "foo"], [1, "bar"], [2, "baz"]],
            $i->enumerate()->toList()
        );
        $this->assertEquals([], iter([])->unwrap()->enumerate()->toList());
    }

    public function test_dedup(): void
    {
        $i = iter([1, 4, 3, 3, 3, 7, 5, 5, 2, 6])->unwrap();
        $this->assertEquals([1, 4, 3, 7, 5, 2, 6], $i->dedup()->toList());
    }

    public function test_concat(): void
    {
        $iter1 = iter([0, 1, 2, 3])->unwrap();
        $iter2 = iter([4, 5, 6, 7])->unwrap()->map(fn($n) => $n * 2)->filter(fn($n) => $n % 3 === 0);
        $iter3 = iter([8, 9, 10])->unwrap()->map(fn($n) => $n);

        $this->assertEquals(
            [0, 1, 2, 3, 12, 8, 9, 10],
            $iter1->concat($iter2, $iter3)->toList()
        );
    }

    public function test_flatten(): void
    {
        $arr = [[0, 1], [2, 3], [4, 5]];
        $this->assertEquals(
            [0, 1, 2, 3, 4, 5],
            iter($arr)->unwrap()->flatten()->toList()
        );
        $this->assertEquals(
            [4, 5],
            iter($arr)->unwrap()->flatten()->toArray()
        );

        $arr = [[0, 1], [2, 3], 4, 5];
        $this->expectException(\UnexpectedValueException::class);
        iter($arr)->unwrap()->flatten()->toList();
    }

    public function test_unique(): void
    {
        $iter = iter([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10])->unwrap()->map(fn($n) => $n % 2);
        $this->assertEquals([0, 1], $iter->unique()->toList());
    }

    public function test_unique_by(): void
    {
        $toZip = iter(["foo", "bar", "baz", "fizz", "buzz", "blob"])->unwrap();
        $iter = iter([0, 1, 2, 3, 4, 5])->unwrap()->zip($toZip)->map(fn($p) => [$p[0] % 3, $p[1]]);

        $this->assertEquals(
            [[0, "foo"], [1, "bar"], [2, "baz"]],
            $iter->uniqueBy(fn($p) => $p[0])->toList()
        );
    }

    public function test_join(): void
    {
        $iter1 = iter(["fizz", "buzz", "bar"])->unwrap()->map(fn($s) => ucfirst($s));
        $iter2 = iter([1, 2, 3, 4, 5, 6])->unwrap()->reject(fn($n) => $n % 2 === 0);

        $this->assertEquals("Fizz | Buzz | Bar", $iter1->join(" | "));
        $this->assertEquals("135", $iter2->join());
    }

    public function test_count_until(): void
    {
        $this->assertEquals(3, iter([1, 2, 3])->unwrap()->countUntil(5));
        $this->assertEquals(3, iter([1, 2, 3, 4, 5])->unwrap()->countUntil(3));
    }

    public function test_zip(): void
    {
        $iterDays = iter(["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"])->unwrap();
        $iterNums = iter([1, 2, 3, 4, 5, 6, 7])->unwrap();

        $this->assertEquals(
            [
                ["Monday", 1],
                ["Tuesday", 2],
                ["Wednesday", 3],
                ["Thursday", 4],
                ["Friday", 5],
                ["Saturday", 6],
                ["Sunday", 7]
            ],
            $iterDays->zip($iterNums)->toList()
        );

        $this->assertEquals(
            [
                ["monday", 2],
                ["tuesday", 4],
                ["wednesday", 6],
                ["thursday", 8],
                ["friday", 10],
                ["saturday", 12],
                ["sunday", 14]
            ],
            $iterDays->map(fn($d) => lcfirst($d))->zip($iterNums->map(fn($n) => 2 * $n))->toList()
        );

        $iterNums2 = $iterNums->concat(iter([8, 9, 10])->unwrap())->filter(fn($n) => $n % 2 === 0);

        $this->assertEquals(
            [
                ["Monday", 2],
                ["Tuesday", 4],
                ["Wednesday", 6],
                ["Thursday", 8],
                ["Friday", 10]
            ],
            $iterDays->zip($iterNums2)->toList()
        );

        $this->assertEquals(
            [
                ["Tuesday", 2],
                ["Thursday", 4],
                ["Saturday", 6],
            ],
            $iterDays->zip($iterNums)->filter(fn($b) => $b[1] % 2 === 0)->toList()
        );
    }

    public function test_unzip(): void
    {
        $iterDays = iter(["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"])->unwrap();
        $iterNums = iter([1, 2, 3, 4, 5, 6, 7])->unwrap();
        $zipped = $iterDays->zip($iterNums);

        $this->assertEquals(
            [["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"], [1, 2, 3, 4, 5, 6, 7]],
            $zipped->unzip()
        );

    }

    public function test_zip_multiple(): void
    {
        $iterDays = iter(["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"])->unwrap();
        $iterNums = iter([1, 2, 3, 4, 5, 6, 7])->unwrap();

        $this->assertEquals(
            $iterDays->zip($iterNums)->toList(),
            $iterDays->zipMultiple($iterNums)->toList()
        );

        $i1 = generate(0, fn($n) => $n + 1);
        $i2 = generate(6, fn($n) => $n + 1);
        $i3 = generate(9, fn($n) => $n + 1);

        $this->assertEquals(
            [
                [0, 6, 9],
                [1, 7, 10],
                [2, 8, 11],
                [3, 9, 12]
            ],
            $i1->zipMultiple($i2, $i3)->take(4)->toList()
        );
    }

    public function test_rewindable_iter(): void
    {
        $i = new RewindableIter((function () {
            for ($i = 0; $i < 10; $i++) {
                yield $i;
            }
        })());

        $l = $i->toList();
        $this->assertEquals([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], $l);

        $l = $i->toList();
        $this->assertEquals([0, 1, 2, 3, 4, 5, 6, 7, 8, 9], $l);

        $i->rewind();
        $a = $i->current();
        $this->assertEquals(0, $a);
    }
}
