<?php
namespace Tests\Controllers\Login\ShowController;

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
		$mMysqli = $this->createMock(mysqli::class);
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
		
		$this->setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		$result = $dbHandler->getConnection();
		
		$this->assertEquals($mMysqli, $result);
	}
}
