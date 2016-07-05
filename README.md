# pipeline-streams

This library support functional-style operations on streams of elements, such as map-reduce transformations on interators and arrays.


[![Build Status](https://travis-ci.org/FabioBatSilva/pipeline-streams.svg?branch=master)](https://travis-ci.org/FabioBatSilva/pipeline-streams)
[![Coverage Status](https://coveralls.io/repos/github/FabioBatSilva/pipeline-streams/badge.svg?branch=master)](https://coveralls.io/github/FabioBatSilva/pipeline-streams?branch=master)

**NOTICE: THIS LIBRARY IS UNDER ACTIVE DEVELOPMENT, USE AT YOUR OWN RISK**

### Installation

```sh
composer require pipeline-streams/pipeline-streams
```


### Generating Streams

This library provides Utility methods for creating streams.

``\Pipeline\Streams`` takes a ``Iterator`` or ``array`` as input source :


```php
<?php

use Pipeline\Streams;

// Create stream from list of values
$stream1 = Streams::of(1, 2, 3, 4, 5);

// Create stream from array
$stream2 = Streams::wrap([1, 2, 3, 4, 5]);

// Create stream from Iterator
$stream3 = Streams::wrap(new ArrayIterator([1, 2, 3, 4, 5]));

```


### forEach

Stream has provided a new method ``forEach`` to iterate each element of the stream.

The following code segment shows how to print 10 numbers using ``forEach`` :

```php
<?php

Streams::wrap(range(1, 10))
    ->forEach(function(int $e) {
        var_dump($e);
    });

```


### map

The ``map`` method is used to map each element to its corresponding result.

The following code segment prints unique squares of numbers using ``map`` :

```php
<?php

Streams::of(3, 2, 2, 3, 7, 3, 5)
    ->map(function(int $i) {
        return $i * $i;
    }
    ->distinct()
    ->forEach(function(int $i) {
        var_dump($i);
    });

```


### filter

The ``filter`` method is used to eliminate elements based on a criteria.

The following code segment prints a count of empty strings using ``filter`` :

```php
<?php

// Get count of empty string
$count = Streams::of("abc", "", "bc", "efg", "abcd","", "jkl")
    ->filter(function(string $str) {
        return ! empty($str);
    }
    ->count();

var_dump($count);

```


### limit

The ``limit`` method is used to reduce the size of the stream.

The following code segment shows how to print 10 numbers using ``limit`` :

```php
<?php

Streams::wrap(range(0, 30))
    ->limit(10)
    ->forEach(function(int $e) {
        var_dump($e);
    });

```


### sorted

The ``sorted`` method is used to sort the stream.

The following code segment shows how to print 10 random numbers in a sorted ``order`` :

```php
<?php

$numbers = range(1, 20);

shuffle($numbers);

Streams::wrap($numbers)
    ->sorted()
    ->limit(10)
    ->forEach(function(int $e) {
        var_dump($e);
    });

```


### Collectors

Collectors are used to combine the result of processing on the elements of a stream.

Most code samples from this section use the following list of persons for demonstration purposes :

```php
<?php

class Person
{
    public $name;
    public $age;

    public function __construct(string $name, int age)
    {
        $this->name = $name;
        $this->age  = $age;
    }

    public function __toString()
    {
        return $this->name;
    }
}

$persons = [
    new Person("Max", 18),
    new Person("Peter", 23),
    new Person("Pamela", 23),
    new Person("David", 12)
];
```

#### asArray
The following code segment shows how to get People's whose names begin with the letter P:

```php
<?php
use Pipeline\Streams;
use Pipeline\Collectors;

$filtered = Streams::wrap($persons)
    ->filter(function(Person $p) {
        return $p->name[0] === 'P';
    })
    ->collect(Collectors::asArray());

// [ Person("Peter", 23), Person("Pamela", 23) ]
```

You can also use the helper method ``Stream#toArray``

```php
<?php
$filtered = Streams::wrap($persons)
    ->filter(function(Person $p) {
        return $p->name[0] === 'P';
    })
    ->toArray();

// [ Person("Peter", 23), Person("Pamela", 23) ]
```


#### groupingBy
The next example groups all persons by age :

```php
<?php

$personsByAge = Streams::wrap($persons)
    ->collect(Collectors::groupingBy(function (Person $p) {
        return $p->age;
    }));

/*
[
    23 : [ Person("Peter", 23), Person("Pamela", 23) ],
    18 : [ Person("Max", 18) ],
    12 : [ Person("David", 12) ]
]
*/
```

#### joining
The next example joins all persons into a single string :

```php
<?php

$phrase = Streams::wrap($persons)
    ->filter(function(Person $p) {
        return $p->age >= 18;
    })
    ->map(function(Person $p) {
        return $p->name;
    })
    ->collect(Collectors::joining(','));

// Max, Peter, Pamela
```



### Word count example

Following code snippet shows how you can write a word count program using the Stream API :

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
$result = Streams::wrap($lines)
    ->filter(function(string $line) {
        return strlen($line) > 1;
    })
    ->map(function(string $line) {
        return trim(strtolower($line));
    })
    ->flatMap(function(string $line) {
        return explode(' ', $line);
    })
    ->collect(Collectors::groupingBy(function (string $word) {
        return $word;
    }, Collectors::counting()));

/*
{
    "copyright": 3,
    "permission": 2,
    "the": 13,
    ....
}
*/
```