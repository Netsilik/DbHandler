<?php
namespace Tests\DbHandler;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use mysqli;
use Exception;
use mysqli_stmt;
use Tests\BaseTestCase;
use Netsilik\DbHandler\DbHandler;
use Tests\Mocks\FunctionOverwrites;


class EnsureConnectedTest extends BaseTestCase
{
    public function test_whenConnectionNotInitialized_thenExceptionThrown()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		self::expectException(Exception::class);
		self::expectExceptionMessage('Connection not initialized');
		
		self::callInaccessibleMethod($dbHandler, '_ensureConnected');
	}
	
	public function test_whenNotConnected_thenNoticeTriggered()
    {
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(false);
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);

		self::callInaccessibleMethod($dbHandler, '_ensureConnected');

		self::assertErrorTriggered(E_USER_NOTICE, 'Connection lost; reconnecting');
	}
}
