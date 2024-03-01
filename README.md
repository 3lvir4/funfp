[![Latest Stable Version](https://poser.pugx.org/3lvir4/funfp/v/stable.png)](https://packagist.org/packages/3lvir4/funfp)
[![CI Status](https://github.com/3lvir4/funfp/workflows/CI/badge.svg)](https://github.com/3lvir4/funfp/actions)

# FunFP - Functional Programming Utilities for PHP

FunFP is a package designed to provide some functional programming paradigms and utils within your
PHP projects.

Key features include a **lot** of iterator *lazy* operations like map, filter, groupBy among others
for fluid manipulation of iterable data, along with some helpers.

Additionally, there is option and result types for handling errors by value and nullable values in
a more fluid manner when PHP doesn't provide a convenient way to do it already.
There is also basic iterators for various string manipulations like iterating over bytes,
characters, lines, words etc...

## Installation

You can add this library as a local, per-project dependency to your project using [Composer](https://getcomposer.org/):

```
composer require 3lvir4/php-range
```

If you only need this library during development, then you should add it as a development-time dependency:

```
composer require --dev 3lvir4/php-range
```
## Footnote

The goal of this library is not to turn PHP into haskell or whatever. I tried to aim at specific
functional tools according to some stuff I wish I needed in some of my PHP projects.
