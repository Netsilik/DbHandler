<?php

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

namespace Netsilik\DbHandler
{
	use Netsilik\Testing\Helpers\FunctionOverwrites;
	
	function mysqli_init()
	{
		FunctionOverwrites::incrementCallCount(__FUNCTION__);
		
		if (FunctionOverwrites::isActive(__FUNCTION__)) {
			return FunctionOverwrites::shiftNextReturnValue(__FUNCTION__);
		}
		
		return \mysqli_init();
	}
}
