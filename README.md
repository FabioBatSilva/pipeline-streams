# Package Versions

This library support functional-style operations on streams of elements, such as map-reduce transformations on interators and arrays.


[![Build Status](https://travis-ci.org/FabioBatSilva/pipeline-streams.svg?branch=master)](https://travis-ci.org/FabioBatSilva/pipeline-streams)
[![Coverage Status](https://coveralls.io/repos/github/FabioBatSilva/pipeline-streams/badge.svg?branch=master)](https://coveralls.io/github/FabioBatSilva/pipeline-streams?branch=master)

### Installation

```sh
composer require pipeline-streams/pipeline-streams
```


```php
<?php

Pipelines::of(range(0, 10))
    ->filter(function(int $e) {
        return $e % 2 == 0;
    })
    ->map(function(int $e) {
        return $e + $e;
    })
    ->filter(function(int $e) {
        return ($e > 0 && $e < 10);
    })
    ->forEach(function(int $e) use ($result) {
        var_dump($e);
    });

    // int(4)
    // int(8)
```


```php
<?php

function readFileLines(string $file) : Iterator
{
    $file = new \SplFileObject($file);

    while ( ! $file->eof()) {
        yield $file->fgets();
    }
}

$lines  = $this->readFileLines('./LICENSE');
$result = Pipelines::wrap($lines)
    ->filter(function(string $line) {
        return strlen($line) > 1;
    })
    ->map(function(string $line) {
        return trim(strtolower($line));
    })
    ->flatMap(function(string $line) {
        return explode(' ', $line);
    })
    ->reduce(function (string $word, array $counters) {
        if (!isset($counters[$word])) {
            $counters[$word] = 0;
        }

        $counters[$word] ++;

        return $counters;
    }, []);

/*
{
    "copyright": 3,
    "permission": 2,
    "the": 13,
    ....
}
*/
```