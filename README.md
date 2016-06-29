# Package Versions

This library support functional-style operations on streams of elements, such as map-reduce transformations on interators and arrays.


[![Build Status](https://travis-ci.org/FabioBatSilva/streams-pipeline.svg?branch=master)](https://travis-ci.org/FabioBatSilva/pipeline)

### Installation

```sh
composer require pipeline/pipeline
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