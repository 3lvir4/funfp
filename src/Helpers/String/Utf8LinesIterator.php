<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Helpers\String;

/**
 * @internal
 */
class Utf8LinesIterator extends Utf8CharsIterator
{
    public function current(): string
    {
        $line = "";
        while (parent::valid() && parent::current() !== PHP_EOL) {
            $line .= parent::current();
            parent::next();
        }
        if ($line !== "") return $line;

        $this->next();
        return $this->current();
    }

    public function next(): void
    {
        while (parent::valid() && parent::current() === PHP_EOL) {
            parent::next();
        }
    }
}