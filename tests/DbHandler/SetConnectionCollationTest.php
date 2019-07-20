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


class SetConnectionCollationTest extends BaseTestCase
{
    public function test_whenMethodCalled_thenQueryExecutedAndSelfReturned()
    {
    	$mMysqli_result = self::createMock(mysqli_result::class);
		
		$mMysqli_stmt = self::createMock(mysqli_stmt::class);
		$mMysqli_stmt->method('execute')->willReturn(true);
		$mMysqli_stmt->method('result_metadata')->willReturn($mMysqli_result);
		
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('prepare')->willReturn($mMysqli_stmt);
		
		$mMysqli_stmt->expects(self::once())->method('execute');
	
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
	
	
		$result = $dbHandler->setConnectionCollation('utf8-bin');
		
		self::assertEquals($dbHandler, $result);
	}
}
