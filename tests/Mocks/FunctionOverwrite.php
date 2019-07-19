<?php
namespace Tests\Mocks;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

class FunctionOverwrite
{
	/**
	 * @var array<string, int>
	 */
	private static $_mockFunctionsActive = [];
	
	/**
	 * @var array<string, int>
	 */
	private static $_functionsCallCount = [];
	
	/**
	 * @param string $functionName       The name of the function to overwrite
	 * @param int    $overwriteCallCount The number of calls that should be routed to the overwritten function method
	 *
	 * @return void
	 */
	public static function setActive($functionName, int $overwriteCallCount) : void
	{
		self::$_mockFunctionsActive[ $functionName ] = $overwriteCallCount;
	}
	
	/**
	 * Check if the function overwrite is (still) active and decrement the overwrite call counter by one
	 *
	 * @param string $functionName The name of the function to overwrite
	 *
	 * @return bool
	 */
	public static function isActive($functionName) : bool
	{
		return (isset(self::$_mockFunctionsActive[ $functionName ]) && 0 < self::$_mockFunctionsActive[ $functionName ]--);
	}
	
	/**
	 * Reset the call count for a function
	 *
	 * @param string $functionName The name of the function to overwrite
	 *
	 * @return void
	 */
	public static function resetCallCount() : void
	{
		self::$_functionsCallCount = [];
	}
	
	/**
	 * Increment the call count for a function
	 *
	 * @param string $functionName The name of the function to overwrite
	 *
	 * @return void
	 */
	public static function incrementCallCount($functionName) : void
	{
		if (!isset(self::$_functionsCallCount[ $functionName ])) {
			self::$_functionsCallCount[ $functionName ] = 1;
		} else {
			self::$_functionsCallCount[ $functionName ]++;
		}
	}
	
	/**
	 * Check the number of times a function is called.
	 *
	 * @param string $functionName The name of the function to overwrite
	 *
	 * @return int The number of times the function was called
	 */
	public static function getCallCount($functionName) : int
	{
		return (isset(self::$_functionsCallCount[ $functionName ]) ? self::$_functionsCallCount[ $functionName ] : 0);
	}
}
