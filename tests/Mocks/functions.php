<?php

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

// Note: we *might* be able to automagically create named functions at runtime (see: https://stackoverflow.com/a/39127886) but it would be scary code.

namespace Netsilik\DbHandler
{
	use Tests\Mocks\FunctionOverwrites;
	
	function mysqli_init()
	{
		FunctionOverwrites::incrementCallCount(__FUNCTION__);
		
		if (FunctionOverwrites::isActive(__FUNCTION__)) {
			return FunctionOverwrites::shiftNextReturnValue(__FUNCTION__);
		}
		
		return \mysqli_init();
	}
}
