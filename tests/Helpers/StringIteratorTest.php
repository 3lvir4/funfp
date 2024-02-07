<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Tests\Helpers;

use Elvir4\FunFp\Helpers\String\AsciiStringIterator;
use Elvir4\FunFp\Helpers\String\Utf8StringIterator;
use PHPUnit\Framework\TestCase;
use function Elvir4\FunFp\constructors\iter;

class StringIteratorTest extends TestCase
{
    public function test_bytes_iter(): void
    {
        $i = iter(new AsciiStringIterator("abcdefgh"))->unwrap();
        $this->assertEquals(["a", "b", "c", "d", "e", "f", "g", "h"], $i->toList());
    }

    public function test_utf8_iter(): void
    {
        $i = iter(new Utf8StringIterator("abcdefgh"))->unwrap();
        $this->assertEquals(["a", "b", "c", "d", "e", "f", "g", "h"], $i->toList());

        $utf = iter(new Utf8StringIterator("šØçt_hǅƕVĞ"))->unwrap();
        $this->assertEquals(["š", "Ø", "ç", "t", "_", "h", "ǅ", "ƕ", "V", "Ğ"], $utf->toList());
    }
}
