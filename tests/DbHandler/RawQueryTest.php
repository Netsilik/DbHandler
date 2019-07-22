<?php
namespace Tests\DbHandler;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use mysqli;
use Exception;
use mysqli_result;
use Netsilik\DbHandler\Interfaces\iDbResult;
use Tests\BaseTestCase;
use Netsilik\DbHandler\DbHandler;


class RawQueryTest extends BaseTestCase
{
    public function test_whenQueryFails_thenExceptionThrown()
    {
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('query')->willReturn(false);
	//	$mMysqli->error = 'fail'; // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591
	//	$mMysqli->errno = 123;    // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		self::expectException(Exception::class);
		self::expectExceptionMessage('query failed:  ()');
		
		$dbHandler->rawQuery('SELECT `id` FROM `table`');
    }
    
    public function test_whenQueryReturnsTrue_thenMinimalPopulatedResultSetReturned()
    {
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('query')->willReturn(true);
	//	$mMysqli->insert_id = 123;   // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591
	//	$mMysqli->affected_rows = 2; // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591

		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		$result = $dbHandler->rawQuery('SELECT `id` FROM `table`');
		
		self::assertInstanceOf(iDbResult::class, $result);
		self::assertEquals(false, $result->getInsertedId()); // see bug above, would be 123 otherwise
		self::assertEquals(false, $result->getAffectedRecords()); // see bug above, would be 2 otherwise
    }
    
    public function test_whenQueryReturnsNonEmptyResult_thenPopulatedResultSetReturned()
    {
    	$mMysqliResult = self::createMock(mysqli_result::class);
		$mMysqliResult->method('fetch_assoc')->willReturn(['id' => 1], ['id' => 2], ['id' => 3], ['id' => 5], ['id' => 8], ['id' => 13]);
		
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('query')->willReturn($mMysqliResult);
	//	$mMysqli->num_rows = 6; // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591

		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		$result = $dbHandler->rawQuery('SELECT `id` FROM `table`');
		$records = $result->fetch();
		
		self::assertInstanceOf(iDbResult::class, $result);
	//	self::assertEquals(6, $result->getRecordCount()); // see bug above
		self::assertArrayHasKey('id', $records[0]);
		self::assertEquals(13, $records[5]['id']);
    }
}
