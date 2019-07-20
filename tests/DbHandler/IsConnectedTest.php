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


class IsConnectedTest extends BaseTestCase
{
    public function test_whenConnectionNotInitialized_thenWarningTriggeredAndFalseReturned()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
		$result = $dbHandler->isConnected();
		
		$this->assertErrorTriggered(E_USER_WARNING, 'Attempted to ping, but connection not initialized');
		
		$this->assertFalse($result);
	}
	
    public function test_whenConnectionOk_thenPingCalled()
    {
		$mMysqli = $this->createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
	
		$mMysqli->expects($this->once())->method('ping');
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		$this->setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		$result = $dbHandler->isConnected();
		
		$this->assertTrue($result);
	}
}
