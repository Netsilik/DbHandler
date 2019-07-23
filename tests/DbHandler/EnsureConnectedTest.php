<?php
namespace Tests\DbHandler;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use mysqli;
use mysqli_stmt;
use mysqli_result;
use Tests\BaseTestCase;
use Netsilik\DbHandler\DbHandler;
use Tests\Mocks\FunctionOverwrites;


class EnsureConnectedTest extends BaseTestCase
{
    public function test_whenNotConnected_thenNoticeTriggered()
    {
		$mMysqli_stmt = self::createMock(mysqli_stmt::class);
		$mMysqli_stmt->method('execute')->willReturn(true);
		$mMysqli_stmt->method('result_metadata')->willReturn(false);
		
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('real_connect')->willReturn(true);
		$mMysqli->method('ping')->willReturn( true, false);
		$mMysqli->method('prepare')->willReturn($mMysqli_stmt);
		
		FunctionOverwrites::setActive('mysqli_init', $mMysqli);
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');


		self::callInaccessibleMethod($dbHandler, '_ensureConnected');

		self::assertErrorTriggered(E_USER_NOTICE, 'Reconnecting to DB');
	}
}
