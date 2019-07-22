<?php
namespace Tests\Mocks;

use ReflectionFunction;
use ReflectionException;
use InvalidArgumentException;


/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

class FunctionOverwrites
{
	/**
	 * @var array<string, int> $_functionsCallCount
	 */
	private static $_functionsCallCount = [];
	
	/**
	 * @var array<string, array<mixed>> $_mockFunctionsReturnValues
	 */
	private static $_mockFunctionsReturnValues = [];
	
	/**
	 * Increment the call count for the specified function
	 *
	 * @param string $functionName The name of the function to overwrite
	 *
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	public static function incrementCallCount(string $functionName) : void
	{
		$functionShortName = self::_getFunctionShortName($functionName);
		
		if (!isset(self::$_functionsCallCount[ $functionShortName ])) {
			self::$_functionsCallCount[ $functionShortName ] = 1;
		} else {
			self::$_functionsCallCount[ $functionShortName ]++;
		}
	}
	
	/**
	 * Get the number of times the specified function is called
	 *
	 * @param string $functionName The name of the function to overwrite
	 *
	 * @return int The number of times the function was called
	 * @throws \InvalidArgumentException
	 */
	public static function getCallCount(string $functionName) : int
	{
		$functionShortName = self::_getFunctionShortName($functionName);
		
		return (isset(self::$_functionsCallCount[ $functionShortName ]) ? self::$_functionsCallCount[ $functionShortName ] : 0);
	}
	
	/**
	 * Check if the function overwrite is (still) active and decrement the overwrite call counter by one
	 *
	 * @param string $functionName The name of the function to overwrite
	 *
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public static function isActive(string $functionName) : bool
	{
		$functionShortName = self::_getFunctionShortName($functionName);
		
		return (isset(self::$_mockFunctionsReturnValues[ $functionShortName ]) && count(self::$_mockFunctionsReturnValues[ $functionShortName ]) > 0);
	}
	
	/**
	 * Enable overwriting the function return values. The specified function will return the given return value(s) until they have all been returned,
	 * after which the overwritten function return the return value from the native function.
	 *
	 * @param string $functionName        The name of the function to overwrite
	 * @param mixed  $returnValue         The value that should be returned by the first call to the overwritten function
	 * @param mixed  ...$nextReturnValues The value(s) that should be returned by each consecutive call to the overwritten function
	 *
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	public static function setActive(string $functionName, $returnValue, ...$nextReturnValues) : void
	{
		$values = [$returnValue];
		array_push($values, ...$nextReturnValues);
		
		self::$_mockFunctionsReturnValues[ self::_getFunctionShortName($functionName) ] = $values;
	}
	
	/**
	 * @param string $functionName The name of the function to get the return value for
	 *
	 * @return mixed The return value
	 * @throws \InvalidArgumentException
	 */
	public static function shiftNextReturnValue(string $functionName)
	{
		return array_shift(self::$_mockFunctionsReturnValues[ self::_getFunctionShortName($functionName) ]);
	}
	
	/**
	 * Get the name of a function without namespace
	 * @param string $functionName
	 *
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	private static function _getFunctionShortName(string $functionName)
	{
		try {
			$rf = new ReflectionFunction($functionName);
		} catch (ReflectionException $e) {
			throw new InvalidArgumentException("Function '" . $functionName . "' does not exist");
		}
		
		return $rf->getShortName();
	}
	
	/**
	 * Reset the all function overwrites (typically called by the tearDown method)
	 *
	 * @return void
	 */
	public static function reset() : void
	{
		self::$_functionsCallCount = [];
		self::$_mockFunctionsReturnValues = [];
	}
}
