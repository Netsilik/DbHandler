<?php

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

namespace Netsilik\DbHandler
{
	
	use ReflectionFunction;
	use Tests\Mocks\FunctionOverwrites;
	
	
	function mysqli_init() : \mysqli
	{
		$functionName = (new ReflectionFunction(__FUNCTION__))->getShortName();
		
		FunctionOverwrites::incrementCallCount($functionName);
		
		if (FunctionOverwrites::isActive($functionName)) {
			return FunctionOverwrites::getNextReturnValue($functionName);
		}
		
		return \mysqli_init();
	}
}
