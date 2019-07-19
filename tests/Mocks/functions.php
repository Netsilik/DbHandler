<?php

/**
 * This file is copyright protected. It is not
 * allowed to adjust, reproduce or sell this
 * product without approval from the author.
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
