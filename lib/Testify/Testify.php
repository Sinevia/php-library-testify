<?php

namespace Testify;

use \Testify\TestifyException;

/**
 * Testify - a micro unit testing framework
 *
 * This is the main class of the framework. Use it like this:
 *
 * @version    1.5.0
 * @author     Martin Angelov
 * @author     Marc-Olivier Fiset
 * @author     Fabien Salathe
 * @link       marco
 * @throws     TestifyException
 * @license    GPL
 */

class Testify
{

    private $tests = array();
    private $stack = array();
    private $fileCache = array();
    private $currentTestCase;
    private $suiteTitle;
    private $suiteResults;

    private $before = null;
    private $after = null;
    private $beforeEach = null;
    private $afterEach = null;

    private $customReporter = null;

    /**
     * As html report need google api(font), while not available in China, this is an option to surrend to gfw(great fire wall)
     *
     * @var bool
     */
    public $gfw = false;

    /**
     * A public object for storing state and other variables across test cases and method calls.
     *
     * @var \StdClass
     */
    public $data = null;

    /**
     * The constructor.
     *
     * @param string $title The suite title
     */
    public function __construct($title)
    {
        $this->suiteTitle = $title;
        $this->data = new \StdClass;
        $this->suiteResults = array('pass' => 0, 'fail' => 0);
    }

    /**
     * Add a test case.
     *
     * @param string $name Title of the test case
     * @param \function $testCase (optional) The test case as a callback
     *
     * @return $this
     */
    public function test($name, \Closure $testCase = null)
    {
        if (is_callable($name)) {
            $testCase = $name;
            $name = "Test Case #" . (count($this->tests) + 1);
        }

        $this->affirmCallable($testCase, "test");

        $this->tests[] = array("name" => $name, "testCase" => $testCase);
        return $this;
    }

    /**
     * Executed once before the test cases are run.
     *
     * @param \function $callback An anonymous callback function
     */
    public function before(\Closure $callback)
    {
        $this->affirmCallable($callback, "before");
        $this->before = $callback;
    }

    /**
     * Executed once after the test cases are run.
     *
     * @param \function $callback An anonymous callback function
     */
    public function after(\Closure $callback)
    {
        $this->affirmCallable($callback, "after");
        $this->after = $callback;
    }

    /**
     * Executed for every test case, before it is run.
     *
     * @param \function $callback An anonymous callback function
     */
    public function beforeEach(\Closure $callback)
    {
        $this->affirmCallable($callback, "beforeEach");
        $this->beforeEach = $callback;
    }

    /**
     * Executed for every test case, after it is run.
     *
     * @param \function $callback An anonymous callback function
     */
    public function afterEach(\Closure $callback)
    {
        $this->affirmCallable($callback, "afterEach");
        $this->afterEach = $callback;
    }

    /**
     * Run all the tests and before / after functions. Calls {@see report} to generate the HTML report page.
     *
     * @param \function $customReporter An anonymous function for creating custom reports used in {@see report}
     *
     * @return $this
     */
    public function run(\Closure $customReporter = null)
    {
        $this->customReporter = $customReporter;
        $arr = array($this);

        if (is_callable($this->before)) {
            call_user_func_array($this->before, $arr);
        }

        foreach ($this->tests as $test) {
            $this->currentTestCase = $test['name'];

            if (is_callable($this->beforeEach)) {
                call_user_func_array($this->beforeEach, $arr);
            }

            // Executing the testcase
            call_user_func_array($test['testCase'], $arr);

            if (is_callable($this->afterEach)) {
                call_user_func_array($this->afterEach, $arr);
            }
        }

        if (is_callable($this->after)) {
            call_user_func_array($this->after, $arr);
        }

        $this->report();

        return $this;
    }

    /**
     * Alias for {@see assertTrue} method.
     *
     * @param boolean $arg The result of a boolean expression
     * @param string $message (optional) Custom message. SHOULD be specified for easier debugging
     * @see Testify->assertTrue()
     *
     * @return boolean
     */
    public function assert($arg, $message = '')
    {
        return $this->assertTrue($arg, $message);
    }

    /**
     * Asserts $arg is an array
     *
     * @param $arg
     * @param $message
     *
     * @return bool
     */
    public function assertArray($arg, $message = '')
    {
        return $this->recordTest(is_array($arg), $message);
    }

    /**
     * Asserts that an array has a specified key.
     *
     * @param mixed $array
     * @param mixed $key
     * @throws Exception
     */
    public function assertArrayHasKey($array, $key, string $message = '')
    {
        $hasKey = (isset($array[$key]) == true);

        return $this->recordTest($hasKey, $message);
    }

    /**
     * Passes if $arg1 == $arg2.
     *
     * @param mixed $arg1
     * @param mixed $arg2
     * @param string $message (optional) Custom message. SHOULD be specified for easier debugging
     *
     * @return boolean
     */
    public function assertEquals($arg1, $arg2, $message = '')
    {
        return $this->recordTest($arg1 == $arg2, $message);
    }

    /**
     * Asserts the method will throw an exception
     *
     * @param $testClass
     * @param $testMethod
     * @param $message
     *
     * @return bool
     */
    public function assertException($testClass, $testMethod, $message = '')
    {
        try {
            $testClass->$testMethod();
        } catch (\Throwable $e) {
            return $this->recordTest(true, $message);
        }

        return $this->recordTest(false, $message);
    }

    /**
     * Passes if given a falsy expression.
     *
     * @param boolean $arg The result of a boolean expression
     * @param string $message (optional) Custom message. SHOULD be specified for easier debugging
     *
     * @return boolean
     */
    public function assertFalse($arg, $message = '')
    {
        return $this->recordTest($arg == false, $message);
    }

    /**
     * Passes if $arg is an element of $arr.
     *
     * @param mixed $arg
     * @param array $arr
     * @param string $message (optional) Custom message. SHOULD be specified for easier debugging
     *
     * @return boolean
     */
    public function assertInArray($arg, array $arr, $message = '')
    {
        return $this->recordTest(in_array($arg, $arr), $message);
    }

    /**
     * Asserts that the passed is a JSON string
     *
     * @param string $arg
     * @return boolean
     */
    public function assertJson($arg, $message = '')
    {
        $json = json_decode($arg);
        $isJson = $json && $arg != $json;
        return $this->recordTest($isJson, $message);
    }

    /**
     * Asserts $arg is not an array
     *
     * @param $arg
     * @param $message
     *
     * @return bool
     */
    public function assertNotArray($arg, $message = '')
    {
        return $this->recordTest((is_array($arg) == false), $message);
    }
    
    /**
     * Asserts that an array has not a specified key.
     *
     * @param mixed $array
     * @param mixed $key
     * @throws Exception
     */
    public function assertNotArrayHasKey($array, $key, string $message = '')
    {
        $hasNoKey = (isset($array[$key]) == false);

        return $this->recordTest($hasNoKey, $message);
    }

    /**
     * Passes if $arg1 != $arg2.
     *
     * @param mixed $arg1
     * @param mixed $arg2
     * @param string $message (optional) Custom message. SHOULD be specified for easier debugging
     *
     * @return boolean
     */
    public function assertNotEquals($arg1, $arg2, $message = '')
    {
        return $this->recordTest($arg1 != $arg2, $message);
    }

    /**
     * Passes if $arg is not an element of $arr.
     *
     * @param mixed $arg
     * @param array $arr
     * @param string $message (optional) Custom message. SHOULD be specified for easier debugging
     *
     * @return boolean
     */
    public function assertNotInArray($arg, array $arr, $message = '')
    {
        return $this->recordTest(!in_array($arg, $arr), $message);
    }

    /**
     * Asserts that the passed is a not JSON string
     *
     * @param string $arg
     * @return boolean
     */
    public function assertNotJson($arg, $message = '')
    {
        $json = json_decode($arg);
        $isJson = $json && $arg != $json;
        return $this->recordTest(($isJson == false), $message);
    }

    /**
     * Asserts that the passed is not a NULL
     *
     * @param string $arg
     * @return boolean
     */
    public function assertNotNull($arg, $message = '')
    {
        $isNull = is_null($arg);
        return $this->recordTest(($isNull == false), $message);
    }

    /**
     * Passes if $arg1 !== $arg2.
     *
     * @param mixed $arg1
     * @param mixed $arg2
     * @param string $message (optional) Custom message. SHOULD be specified for easier debugging
     *
     * @return boolean
     */
    public function assertNotSame($arg1, $arg2, $message = '')
    {
        return $this->recordTest($arg1 !== $arg2, $message);
    }

    /**
     * Asserts string does not contain substring
     * @param string $string
     * @param string $substring
     * @return boolean
     */
    public function assertNotStringContainsString(string $string, string $substring, string $message = '')
    {
        $containsString = strpos($string, $substring) === false ? false : true;

        return $this->recordTest($containsString == false, $message);
    }

    /**
     * Asserts string does not contain substring ignoring case
     * @param string $string
     * @param string $substring
     * @return boolean
     */
    public function assertNotStringContainsStringIgnoringCase(string $string, string $substring, string $message = '')
    {
        $containsString = stripos($string, $substring) === false ? false : true;

        return $this->recordTest($containsString == false, $message);
    }

    /**
     * Asserts that the passed is a NULL
     *
     * @param string $arg
     * @return boolean
     */
    public function assertNull($arg, $message = '')
    {
        $isNull = is_null($arg);
        return $this->recordTest($isNull, $message);
    }

    /**
     * Asserts a regular expression
     *
     * @param $regEx
     * @param $arg
     * @param $message
     *
     * @return bool
     */
    public function assertRegExpr($pattern, $string, $message = '')
    {
        $pattern = "/" . $pattern . "/i";
        $test = preg_match($pattern, $string);
        return $this->recordTest($test, $message);
    }

    /**
     * Passes if $arg1 === $arg2.
     *
     * @param mixed $arg1
     * @param mixed $arg2
     * @param string $message (optional) Custom message. SHOULD be specified for easier debugging
     *
     * @return boolean
     */
    public function assertSame($arg1, $arg2, $message = '')
    {
        return $this->recordTest($arg1 === $arg2, $message);
    }

    /**
     * Asserts string contains substring
     * @param string $string
     * @param string $substring
     * @return boolean
     */
    public function assertStringContainsString(string $string, string $substring, string $message = '')
    {
        $containsString = strpos($string, $substring) === false ? false : true;

        return $this->recordTest($containsString == true, $message);
    }

    /**
     * Asserts string contains substring ignoring case
     * @param string $string
     * @param string $substring
     * @return boolean
     */
    public function assertStringContainsStringIgnoringCase(string $string, string $substring, string $message = '')
    {
        $containsString = stripos($string, $substring) === false ? false : true;

        return $this->recordTest($containsString == true, $message);
    }

    /**
     * Passes if given a truthfull expression.
     *
     * @param boolean $arg The result of a boolean expression
     * @param string $message (optional) Custom message. SHOULD be specified for easier debugging
     *
     * @return boolean
     */
    public function assertTrue($arg, $message = '')
    {
        return $this->recordTest($arg == true, $message);
    }

    /**
     * Unconditional pass.
     *
     * @param string $message (optional) Custom message. SHOULD be specified for easier debugging
     *
     * @return boolean
     */
    public function pass($message = '')
    {
        return $this->recordTest(true, $message);
    }

    /**
     * Unconditional fail.
     *
     * @param string $message (optional) Custom message. SHOULD be specified for easier debugging
     *
     * @return boolean
     */
    public function fail($message = '')
    {
        // This check fails every time
        return $this->recordTest(false, $message);
    }

    /**
     * Generates a pretty CLI, HTML5 or custom report of the test suite status. Called implicitly by {@see run}.
     *
     * @return $this
     */
    public function report()
    {
        $title = $this->suiteTitle;
        $suiteResults = $this->suiteResults;
        $cases = $this->stack;

        if (is_callable($this->customReporter)) {
            call_user_func($this->customReporter, $title, $suiteResults, $cases);
        } else if (php_sapi_name() === 'cli') {
            include dirname(__FILE__) . '/testify.report.cli.php';
        } else {
            include dirname(__FILE__) . '/testify.report.html.php';
        }

        return $this;
    }

    /**
     * A helper method for recording the results of the assertions in the internal stack.
     *
     * @param boolean $pass If equals true, the test has passed, otherwise failed
     * @param string $message (optional) Custom message
     *
     * @return boolean
     */
    private function recordTest($pass, $message = '')
    {
        if (!array_key_exists($this->currentTestCase, $this->stack) ||
            !is_array($this->stack[$this->currentTestCase])) {

            $this->stack[$this->currentTestCase]['tests'] = array();
            $this->stack[$this->currentTestCase]['pass'] = 0;
            $this->stack[$this->currentTestCase]['fail'] = 0;
        }

        $bt = debug_backtrace();
        $source = $this->getFileLine($bt[1]['file'], $bt[1]['line'] - 1);
        $bt[1]['file'] = basename($bt[1]['file']);

        $result = $pass ? "pass" : "fail";
        $this->stack[$this->currentTestCase]['tests'][] = array(
            "name" => $message,
            "type" => $bt[1]['function'],
            "result" => $result,
            "line" => $bt[1]['line'],
            "file" => $bt[1]['file'],
            "source" => $source,
        );

        $this->stack[$this->currentTestCase][$result]++;
        $this->suiteResults[$result]++;

        return $pass;
    }

    /**
     * Internal method for fetching a specific line of a text file. With caching.
     *
     * @param string $file The file name
     * @param integer $line The line number to return
     *
     * @return string
     */
    private function getFileLine($file, $line)
    {
        if (!array_key_exists($file, $this->fileCache)) {
            $this->fileCache[$file] = file($file);
        }

        return trim($this->fileCache[$file][$line]);
    }

    /**
     * Internal helper method for determine whether a variable is callable as a function.
     *
     * @param mixed $callback The variable to check
     * @param string $name Used for the error message text to indicate the name of the parent context
     * @throws TestifyException if callback argument is not a function
     */
    private function affirmCallable(&$callback, $name)
    {
        if (!is_callable($callback)) {
            throw new TestifyException("$name(): is not a valid callback function!");
        }
    }

    /**
     * Alias for {@see assertEquals}.
     *
     * @deprecated Not recommended, use {@see assertEquals}
     * @param mixed $arg1
     * @param mixed $arg2
     * @param string $message (optional) Custom message. SHOULD be specified for easier debugging
     *
     * @return boolean
     */
    public function assertEqual($arg1, $arg2, $message = '')
    {
        return $this->assertEquals($arg1, $arg2, $message);
    }

    /**
     * Alias for {@see assertSame}.
     *
     * @deprecated Not recommended, use {@see assertSame}
     * @param mixed $arg1
     * @param mixed $arg2
     * @param string $message (optional) Custom message. SHOULD be specified for easier debugging
     *
     * @return boolean
     */
    public function assertIdentical($arg1, $arg2, $message = '')
    {
        return $this->recordTest($arg1 === $arg2, $message);
    }

    /**
     * Alias for {@see run} method.
     *
     * @see Testify->run()
     *
     * @return $this
     */
    public function __invoke()
    {
        return $this->run();
    }
}
