<?php

declare(strict_types=1);

namespace Elvir4\FunFp\Helpers\String;

/**
 * @internal
 */
class Utf8WordsIterator extends Utf8CharsIterator
{
    public function current(): string
    {
        $word = "";
        while (parent::valid() && !ctype_space(parent::current())) {
            $word .= parent::current();
            parent::next();
        }
        if ($word !== "") return $word;

        $this->next();
        return $this->current();
    }

    public function next(): void
    {
        while (parent::valid() && ctype_space(parent::current())) {
            parent::next();
        }
    }
}