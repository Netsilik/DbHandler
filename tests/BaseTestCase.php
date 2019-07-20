<?php
namespace Tests;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use Closure;
use ErrorException;
use ReflectionClass;
use Tests\Mocks\FunctionOverwrites;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\TestCase AS phpUnitTestCase;


abstract class BaseTestCase extends phpUnitTestCase
{
	const ERROR_CONTANT_NAMES = [
		E_WARNING      => 'E_WARNING',
		E_NOTICE       => 'E_NOTICE',
		E_USER_WARNING => 'E_USER_WARNING',
		E_USER_NOTICE  => 'E_USER_NOTICE',
		E_STRICT       => 'E_STRICT',
	];
	
	/**
	 * @var array $_errors
	 */
	private $_errors = [];
	
	/**
	 * {@inheritDoc}
	 */
	protected function setUp() : void
	{
		$this->_setupErrorHandler();
	}
	
	/**
	 * Call a private or protected method
	 *
	 * @param object $instance   The instance of the class to call the specified method on
	 * @param string $method     The name of the method to call on the provided instance
	 * @param array  $parameters The parameters to pass into the specified method
	 *
	 * @return mixed
	 * @throws \ReflectionException
	 */
    protected function callInaccessibleMethod($instance, $method, array $parameters = [])
    {
        $class = new ReflectionClass(get_class($instance));
        $method = $class->getMethod($method);
        $method->setAccessible(true);
		
        return $method->invokeArgs($instance, $parameters);
    }
    
	/**
	 * {@inheritDoc}
	 */
	public function tearDown() : void
	{
		$this->_restoreErrorHandler();
		
		FunctionOverwrites::reset();
	}
	
	/**
	 * @param int    $errorType    The type of error to that we expected. See {@link https://www.php.net/manual/en/errorfunc.constants.php} for more
	 *                             details.
	 * @param string $errorMessage The message of the error we expected
	 */
	public function assertErrorTriggered(int $errorType, string $errorMessage) : void
	{
		$errorTypeFound = false;
		$errorMessageFound = false;
		
		foreach ($this->_errors as $error) {
			if ($error['errorType'] === $errorType && $error['errorMessage'] === $errorMessage) {
				return; // All ok!
			}
			
			if (!$errorTypeFound && $error['errorType'] === $errorType) {
				$errorTypeFound = true;
			}
			if (!$errorMessageFound && $error['errorMessage'] === $errorMessage) {
				$errorMessageFound = true;
			}
		}
		
		// Build a resonably detailed failure message
		if (!$errorTypeFound) {
			$errorChunks[] = 'an error of type ' . self::ERROR_CONTANT_NAMES[$errorType];
		}
		if (!$errorMessageFound) {
			$errorChunks[] = "an error message equal to '{$errorMessage}'";
		}
		
		$notFoundChunk = (count($errorChunks) === 2 ? 'either ' : '') . implode(' or ', $errorChunks);
		$foundChunk = (count($errorChunks) === 1 ? ' (we did see the correct error ' . ($errorTypeFound ? 'type' : 'message') . ')' : '');
		
		$this->fail('Failed asserting that ' . $notFoundChunk . ' was triggered' . $foundChunk . '.');
	}
	
	/**
	 * Make sure we can assert that Warnings and Notices are triggered, without them being fatal
	 *
	 * @return void
	 */
	private function _setupErrorHandler() : void
	{
		$this->_errors = []; // Reset errors at start of test case run
		
		$errorTypes = 0;
		if (!Notice::$enabled) { // The value from convertNoticesToExceptions in phpunit.xml
			$errorTypes |= E_NOTICE | E_USER_NOTICE | E_STRICT;
		}
		if (!Warning::$enabled) { // The value from convertWarningsToExceptions in phpunit.xml
			$errorTypes |= E_WARNING | E_USER_WARNING;
		}
		
		if ($errorTypes <> 0) {
			set_error_handler(function(int $errorType, string $errorMessage, string $errorFile, int $errorLine) use ($errorTypes) : bool {
				if (($errorType & $errorTypes) !== $errorType) { // We are not configured to handle this error -> imitate PHPUnit default behaviour
					// Note: since PHPUnit\Framework\TestCase::$useErrorHandler is private (and no getter exists), we *assume* the value
					//       of the phpunit.xml flag 'convertErrorsToExceptions' is set to true.
					throw new ErrorException($errorMessage, $errorType, $errorType, $errorFile, $errorLine);
				}
				
				$this->_errors[] = [
					'errorType'    => $errorType,
					'errorMessage' => $errorMessage,
				];
				
				return true; // Yes, error propagation should stop here
			}, ~0);
		}
	}
	
	/**
	 * Restore the original error handler
	 *
	 * @return void
	 */
	private function _restoreErrorHandler() : void
	{
		set_error_handler($currentErrorHandler = set_error_handler(function() { return false; })); // Get the currently active error handler
		
		if ($currentErrorHandler instanceof Closure) { // Restore previous error handler if it is a closure
			restore_error_handler();
		}
	}
}
