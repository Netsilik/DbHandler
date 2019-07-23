<?php
namespace Tests\DbResult\AbstractDbResult;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use Tests\BaseTestCase;
use Tests\Mocks\AbstractDbResultTestWrapper;


class GetQueryTimeTest extends BaseTestCase
{
    public function test_whenMethodCalled_thenFormattedQueryTimeReturned()
    {
		$dbResult = new AbstractDbResultTestWrapper();
	
		self::setInaccessibleProperty($dbResult, '_queryTime', 1234.567);
		
		$result = $dbResult->getQueryTime();
		
		self::assertEquals('1,234.5670', $result);
    }
}
