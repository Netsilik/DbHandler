<?php
namespace Tests\Mocks;

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
	 */
	public static function incrementCallCount(string $functionName) : void
	{
		if (!isset(self::$_functionsCallCount[ $functionName ])) {
			self::$_functionsCallCount[ $functionName ] = 1;
		} else {
			self::$_functionsCallCount[ $functionName ]++;
		}
	}
	
	/**
	 * Get the number of times the specified function is called
	 *
	 * @param string $functionName The name of the function to overwrite
	 *
	 * @return int The number of times the function was called
	 */
	public static function getCallCount(string $functionName) : int
	{
		return (isset(self::$_functionsCallCount[ $functionName ]) ? self::$_functionsCallCount[ $functionName ] : 0);
	}
	
	/**
	 * Check if the function overwrite is (still) active and decrement the overwrite call counter by one
	 *
	 * @param string $functionName The name of the function to overwrite
	 *
	 * @return bool
	 */
	public static function isActive(string $functionName) : bool
	{
		return (isset(self::$_mockFunctionsReturnValues[ $functionName ]) && count(self::$_mockFunctionsReturnValues[ $functionName ]) > 0);
	}
	
	/**
	 * Enable overwriting the function return values. The specified function will return the given return value(s) until they have all been returned,
	 * after which the overwritten function return the return value from the native function.
	 *
	 * @param string $functionName        The name of the function to overwrite
	 * @param mixed  $returnValue         The value that should be returned by the rist call to the overwritten function
	 * @param mixed  ...$nextReturnValues The value(s) that should be returned by each consecutive call to the overwritten function
	 *
	 * @return void
	 */
	public static function setActive(string $functionName, $returnValue, ...$nextReturnValues) : void
	{
		$values = [$returnValue];
		array_push($values, ...$nextReturnValues);
		
		self::$_mockFunctionsReturnValues[ $functionName ] = $values;
	}
	
	/**
	 * @param string $functionName The name of the function to get the return value for
	 *
	 * @return mixed The return value
	 */
	public static function shiftNextReturnValue(string $functionName)
	{
		return array_shift(self::$_mockFunctionsReturnValues[ $functionName ]);
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
