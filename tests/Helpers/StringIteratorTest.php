<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Tests\Helpers;

use Elvir4\FunFp\Helpers\String\AsciiStringIterator;
use Elvir4\FunFp\Helpers\String\BytesIterator;
use Elvir4\FunFp\Helpers\String\Utf8CharsIterator;
use Elvir4\FunFp\Helpers\String\Utf8LinesIterator;
use Elvir4\FunFp\Helpers\String\Utf8WordsIterator;
use PHPUnit\Framework\TestCase;
use function Elvir4\FunFp\constructors\iter;

class StringIteratorTest extends TestCase
{
    public function test_ascii_iter(): void
    {
        $i = iter(new AsciiStringIterator("abcdefgh"))->unwrap();
        $this->assertEquals(["a", "b", "c", "d", "e", "f", "g", "h"], $i->toList());
    }

    public function test_bytes_iter(): void
    {
        $i = iter(new BytesIterator("hello world!"))->unwrap();
        $this->assertEquals([104, 101, 108, 108, 111, 32, 119, 111, 114, 108, 100, 33] ,$i->toList());
        $this->assertEquals([], iter(new BytesIterator(""))->unwrap()->toList());
    }

    public function test_utf8_iter(): void
    {
        $i = iter(new Utf8CharsIterator("abcdefgh"))->unwrap();
        $this->assertEquals(["a", "b", "c", "d", "e", "f", "g", "h"], $i->toList());

        $utf = iter(new Utf8CharsIterator("šØçt_hǅƕVĞ"))->unwrap();
        $this->assertEquals(["š", "Ø", "ç", "t", "_", "h", "ǅ", "ƕ", "V", "Ğ"], $utf->toList());
    }

    public function test_words_iter(): void
    {
        $i = iter(new Utf8WordsIterator(" how many  wordš do   you\n have ƕere\tafter "))->unwrap();
        $this->assertEquals(["how", "many", "wordš", "do", "you", "have", "ƕere", "after"], $i->toList());
        $this->assertEquals(["foo"], iter(new Utf8WordsIterator("foo"))->unwrap()->toList());
        $this->assertEquals([], iter(new Utf8WordsIterator(""))->unwrap()->toList());
    }

    public function test_lines_iter(): void
    {
        $i = iter(new Utf8LinesIterator("First line!\nSecond line!!\nThird line...\n\nFifth line?"))->unwrap();
        $this->assertEquals(["First line!", "Second line!!", "Third line...", "Fifth line?"], $i->toList());
        $this->assertEquals(["Just one line..."], iter(new Utf8LinesIterator("Just one line..."))->unwrap()->toList());
        $this->assertEquals([], iter(new Utf8LinesIterator(""))->unwrap()->toList());
    }
}
