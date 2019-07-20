<?php
namespace Tests\Controllers\Login\ShowController;

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


class ConnectTest extends BaseTestCase
{
    public function test_whenAllGoesWell_thenThisReturned()
    {
		$mMysqli_result = $this->createMock(mysqli_result::class);
		
		$mMysqli_stmt = $this->createMock(mysqli_stmt::class);
		$mMysqli_stmt->method('execute')->willReturn(true);
		$mMysqli_stmt->method('result_metadata')->willReturn($mMysqli_result);
		
		$mMysqli = $this->createMock(mysqli::class);
		$mMysqli->method('real_connect')->willReturn(true);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('prepare')->willReturn($mMysqli_stmt);
		
		FunctionOverwrites::setActive('mysqli_init', [$mMysqli]);
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
		$result = $dbHandler->connect();
		
        $this->assertEquals($dbHandler, $result);
    }
}
