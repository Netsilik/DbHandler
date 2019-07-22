<?php
namespace Tests\DbHandler;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use mysqli;
use Tests\BaseTestCase;
use Netsilik\DbHandler\DbHandler;


class GetConnectionTest extends BaseTestCase
{
    public function test_whenMethodCalled_thenValidInstanceReturned()
    {
		$mMysqli = self::createMock(mysqli::class);
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
		
		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		$result = $dbHandler->getConnection();
		
		self::assertEquals($mMysqli, $result);
	}
}
