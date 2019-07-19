<?php

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

namespace Netsilik\DbHandler
{
	use Tests\Mocks\FunctionOverwrite;
	
	function mysqli_init() : \mysqli
	{
		FunctionOverwrite::incrementCallCount('mysqli_init');
		
		if (FunctionOverwrite::isActive('mysqli_init')) {
			return \mysqli_init();
		}
		
		return \mysqli_init();
	}
}
