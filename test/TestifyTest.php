<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Testify\Testify;

$tf = new Testify("Testify test himself");
$test = new Testify("//");

$tf->before(function($tf) use($test) {
	$test->data->arr = array(1, 2, 3);
});

$tf->test("Pass/Fail test", function($tf) use($test)
{
	$tf->assert(true, "To be sure that initial test pass !");
	$tf->assertFalse(false);

	$tf->assert($test->pass());
	$tf->assertFalse(!$test->pass());
	$tf->assert(!$test->fail());
	$tf->assertFalse($test->fail());
});

$tf->test("Basic assert test", function($tf) use($test)
{
	$tf->assert($test->assert(true));
	$tf->assert(!$test->assert(false));

	$tf->assertFalse($test->assert(false));
	$tf->assertFalse(!$test->assert(true));
});
	
$tf->test("assertArrayHasKey/assertNotArrayHasKey test", function($tf) use($test)
{
    $tf->assert($test->assertArrayHasKey(['body'=>'Hello world'], 'body'));
    $tf->assertFalse($test->assertNotArrayHasKey(['body'=>'Hello world'], 'body'));
});


$tf->test("assertEquals/assertNotEquals test", function($tf) use($test)
{
	$tf->assert($test->assertEquals(1, 1));
	$tf->assert($test->assertEquals(-1337, '-1337'));
	$tf->assert($test->assertEquals(42.0, 42));
	$tf->assert($test->assertEquals(0, null));
	$tf->assert($test->assertEquals(0, ""));
	$tf->assert($test->assertEquals(1, true));
	$tf->assert($test->assertEquals(array(0,1,1), array(false,"1",true)));
	$tf->assert($test->assertEquals(new \StdClass, (object)array()));
	$tf->assert($test->assertNotEquals(-1, ""));

	$tf->assertFalse($test->assertEquals(-1, ""));
	$tf->assertFalse($test->assertEquals(array(1), 1));
	$tf->assertFalse($test->assertEquals(array(9,8), (object)array(9,8)));
	$tf->assertFalse($test->assertNotEquals(1.0, 1));
});

$tf->test("assertSame/assertNotSame test", function($tf) use($test)
{
	$tf->assert($test->assertSame(-1, -1));
	$tf->assert($test->assertNotSame(-1, -1.0));
	$tf->assert($test->assertSame(2E10, 2E10));
	$tf->assert($test->assertSame("\$", '$'));
	$tf->assert($test->assertSame(array(0,1,true), array(0,1,true)));
	$tf->assert($test->assertSame(255, 0xFF));
	$tf->assert($test->assertSame((int) 42.1, 42));
	$tf->assert($test->assertNotSame(new \StdClass, new \StdClass));

	$tf->assertFalse($test->assertSame(1, "1"));
	$tf->assertFalse($test->assertSame(1.0, 1));
	$tf->assertFalse($test->assertSame(2, (float)2));
	$tf->assertFalse($test->assertSame(new \StdClass, (object)array()));
});

$tf->test("assertInArray/assertNotInArray test", function($tf) use($test)
{
	$arr = array(1, 2, null, false, "1", "2");

	$tf->assert($test->assertInArray(1, $arr));
	$tf->assert($test->assertInArray(true, $arr));
	$tf->assert($test->assertInArray(false, $arr));
	$tf->assert($test->assertInArray(0, $arr));
	$tf->assert($test->assertNotInArray(3, $arr));
	$tf->assert($test->assertNotInArray("str", $arr));

	$tf->assertFalse($test->assertInArray(-1, $arr));
	$tf->assertFalse($test->assertInArray(array(0), $arr));
});

$tf->test("data set test", function($tf) use($test)
{
	$tf->assert($test->assertInArray(2, $test->data->arr));
	$tf->assert($test->assertInArray(3, $test->data->arr));
	$tf->assert($test->assertNotInArray(9, $test->data->arr));
});

$tf->test("assertJson/assertNotJson test", function($tf) use($test)
{
	$tf->assert($test->assertJson('{"a":5}')); // bool(true)
	$tf->assert($test->assertJson('[1,2,3]')); // bool(true)
	$tf->assertFalse($test->assertJson('1')); // bool(false)
	$tf->assertFalse($test->assertJson('1.5')); // bool(false)
	$tf->assertFalse($test->assertJson('true')); // bool(false)
	$tf->assertFalse($test->assertJson('false')); // bool(false)
	$tf->assertFalse($test->assertJson('null')); // bool(false)
	$tf->assertFalse($test->assertJson('hello')); // bool(false)
	$tf->assertFalse($test->assertJson('')); // bool(false)
	
	$tf->assertFalse($test->assertNotJson('{"a":5}')); // bool(true)
	$tf->assertFalse($test->assertNotJson('[1,2,3]')); // bool(true)
	$tf->assert($test->assertNotJson('1')); // bool(false)
	$tf->assert($test->assertNotJson('1.5')); // bool(false)
	$tf->assert($test->assertNotJson('true')); // bool(false)
	$tf->assert($test->assertNotJson('false')); // bool(false)
	$tf->assert($test->assertNotJson('null')); // bool(false)
	$tf->assert($test->assertNotJson('hello')); // bool(false)
	$tf->assert($test->assertNotJson('')); // bool(false)
});

$tf->test("assertNull/assertNotNull test", function($tf) use($test)
{
	$tf->assert($test->assertNull(NULL)); // bool(true)
	$tf->assert($test->assertNotNull('')); // bool(true)
		    
	$tf->assertFalse($test->assertNull('')); // bool(false)
	$tf->assertFalse($test->assertNotNull(NULL)); // bool(false)
});

$tf->test("assertArray/assertNotArray test", function($tf) use($test)
{
    $tf->assert($test->assertArray([1,2])); // bool(true)
    $tf->assert($test->assertNotArray('')); // bool(true)
		    
    $tf->assertFalse($test->assertArray('')); // bool(false)
    $tf->assertFalse($test->assertNotArray([1,2])); // bool(false)
});

$tf->test("assertException test", function($tf) use($test)
{
    $tf->assert($test->assertException((new TestException), 'exceptionThrowingMethod')); // bool(true)
});

$tf->test("assertStringContainsString/assertNotStringContainsString test", function($tf) use($test)
{
    $tf->assertTrue($test->assertStringContainsString('Hello world', 'world'));
    $tf->assertTrue($test->assertNotStringContainsString('Hello world', 'car'));
});


$tf->test("assertStringContainsString/assertNotStringContainsStringIgnoringCase test", function($tf) use($test)
{
    $tf->assertTrue($test->assertStringContainsStringIgnoringCase('Hello World', 'world'));
    $tf->assertFalse($test->assertNotStringContainsStringIgnoringCase('Hello World', 'world'));
});




$tf();

class TestException {
	function exceptionThrowingMethod(){
		throw new \RuntimeException("This is a test exception");
	}
}
