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


class IsConnectedTest extends BaseTestCase
{
    public function test_whenConnectionNotInitialized_thenFalseReturned()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
		$result = $dbHandler->isConnected();
		
		self::assertFalse($result);
	}
	
    public function test_whenConnectionOk_thenPingCalled()
    {
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
	
		$mMysqli->expects(self::once())->method('ping');
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		$result = $dbHandler->isConnected();
		
		self::assertTrue($result);
	}
}
