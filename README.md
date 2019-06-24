Testify - a micro unit testing framework
========================================
Testify is a micro unit testing framework for PHP 5.3+. It strives for elegance instead of feature bloat. Testing your code is no longer a chore - it's fun again.

![No Dependencies](https://img.shields.io/badge/no-dependencies-success.svg)
[![Build status][build-status-master-image]][build-status-master]
[![GitHub stars](https://img.shields.io/github/stars/Sinevia/php-library-testify.svg?style=social&label=Star&maxAge=2592000)](https://GitHub.com/Sinevia/php-library-testify/stargazers/)
[![HitCount](http://hits.dwyl.io/Sinevia/badges.svg)](http://hits.dwyl.io/Sinevia/badges)

[build-status-master]: https://travis-ci.com/Sinevia/php-library-testify
[build-status-master-image]: https://api.travis-ci.com/Sinevia/php-serverless.svg?branch=master

## Requirements

* PHP 5.3+ is required

## Installation ##

- Via Composer. [Composer](http://getcomposer.org/) (recommended)
```
composer require sinevia/php-library-testify
```

- Manually. Download and add to your project


Usage
-----
Here is an example for a test suite with two test cases:

```php
require 'vendor/autoload.php';

use Math\MyCalc;
use Testify\Testify;

$tf = new Testify("MyCalc Test Suite");

$tf->beforeEach(function($tf) {
	$tf->data->calc = new MyCalc(10);
});

$tf->test("Testing the add() method", function($tf) {
	$calc = $tf->data->calc;

	$calc->add(4);
	$tf->assert($calc->result() == 14);

	$calc->add(-6);
	$tf->assertEquals($calc->result(), 8);
});

$tf->test("Testing the mul() method", function($tf) {
	$calc = $tf->data->calc;

	$calc->mul(1.5);
	$tf->assertEquals($calc->result(), 12);

	$calc->mul(-1);
	$tf->assertEquals($calc->result(), -12);
});

$tf();
```

# Documentation

 * `__construct( string $title )` - The constructor
 * `test( string $name, [Closure $testCase = null] )` - Add a test case.
 * `before( Closure $callback )` - Executed once before the test cases are run
 * `after( Closure $callback )` - Executed once after the test cases are run
 * `beforeEach( Closure $callback )` - Executed for every test case, before it is run
 * `afterEach( Closure $callback )` - Executed for every test case, after it is run
 * `run( )` - Run all the tests and before / after functions. Calls report() to generate the HTML report page
 * `assert( boolean $arg, [string $message = ''] )` - Alias for assertTrue() method
 * `assertArray( mixed $arg, [string $message = ''] )` - Passes if $arg is an array
 * `assertArrayHasKey( mixed $key, array $array, [string $message = ''] )` - Passes if $array has a $key
 * `assertArrayNotHasKey( mixed $key, array $array, [string $message = ''] )` - Passes if $array has not a $key
 * `assertArray( mixed $arg, [string $message = ''] )` - Passes if $arg is an array
 * `assertEquals( mixed $arg1, mixed $arg2, string [string $message = ''] )` - Passes if $arg1 == $arg2
 * `assertException( object $classInstance, string $methodName, [string $message = ''] )` - Passes if method throws Exception
 * `assertFalse( boolean $arg, [string $message = ''] )` - Passes if given a falsy expression
 * `assertInArray( mixed $arg, array $arr, string [string $message = ''] )` - Passes if $arg is an element of $arr
 * `assertJson( string $arg, string [string $message = ''] )` - Passes if $arg is a JSON string
 * `assertNotArray( mixed $arg, [string $message = ''] )` - Passes if $arg is not an array
 * `assertNotEquals( mixed $arg1, mixed $arg2, string [string $message = ''] )` - Passes if $arg1 != $arg2
 * `assertNotInArray( mixed $arg, array $arr, string [string $message = ''] )` - Passes if $arg is not an element of $arr
 * `assertNotJson( string $arg, string [string $message = ''] )` - Passes if $arg is not a JSON string
 * `assertNotNull( string $arg, string [string $message = ''] )` - Passes if $arg is not a NULL
 * `assertNotSame( mixed $arg1, mixed $arg2, string [string $message = ''] )` - Passes if $arg1 !== $arg2
 * `assertNull( string $arg, string [string $message = ''] )` - Passes if $arg is a NULL
 * `assertRegExpr( string $arg1, string $arg2, [string $message = ''] )` - Passes if $arg1 is matched in $arg2
 * `assertSame( mixed $arg1, mixed $arg2, string [string $message = ''] )` - Passes if $arg1 === $arg2
 * `assertTrue( boolean $arg, [string $message = ''] )` - Passes if given a truthfull expression
 * `pass( string [string $message = ''] )` - Unconditional pass
 * `fail( string [string $message = ''] )` - Unconditional fail
 * `report( )` - Generates a pretty CLI or HTML5 report of the test suite status. Called implicitly by run()
 * `__invoke( )` - Alias for run() method

