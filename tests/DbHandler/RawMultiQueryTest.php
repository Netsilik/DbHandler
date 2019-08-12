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


class RawMultiQueryTest extends BaseTestCase
{
    public function test_whenFirstQueryFails_thenExceptionThrown()
    {
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('multi_query')->willReturn(false);
	//	$mMysqli->error = 'fail'; // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591
	//	$mMysqli->errno = 123;    // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		self::expectException(Exception::class);
		self::expectExceptionMessage('first query failed: (' . null . ') ' . null);
		
		$dbHandler->rawMultiQuery('SELECT `id` FROM `table`;SELECT `id` FROM `otherTable`;');
    }
    
    public function test_whenQueriesOk_thenMinimalPopulatedResultSetReturned()
    {
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('multi_query')->willReturn(true);
		$mMysqli->method('store_result')->willReturn(false);
		$mMysqli->method('more_results')->willReturn(true, false);
		$mMysqli->method('next_result')->willReturn(true, false);
	//	$mMysqli->errno = 123;   // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591
	//	$mMysqli->error = 'fail'; // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591

		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		$result = $dbHandler->rawMultiQuery('SELECT `id` FROM `table`;SELECT `id` FROM `otherTable`;');
		
		self::assertIsArray($result);
		self::assertCount(2, $result);
		self::assertInstanceOf(iDbResult::class, $result[0]);
		self::assertInstanceOf(iDbResult::class, $result[1]);
		self::assertEquals(false, $result[0]->getInsertedId()); // see bug above, would be 123 otherwise
		self::assertEquals(false, $result[0]->getAffectedRecords()); // see bug above, would be 2 otherwise
		self::assertEquals(false, $result[1]->getInsertedId()); // see bug above, would be 123 otherwise
		self::assertEquals(false, $result[1]->getAffectedRecords()); // see bug above, would be 2 otherwise
    }
    
    public function test_whenQueryReturnsNonEmptyResult_thenPopulatedResultSetReturned()
    {
    	$mMysqliResult = self::createMock(mysqli_result::class);
		
    	$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('multi_query')->willReturn(true);
		$mMysqli->method('store_result')->willReturn(false);
		$mMysqli->method('more_results')->willReturn(true, false);
		$mMysqli->method('next_result')->willReturn(true, $mMysqliResult);
	//	$mMysqli->errno = 123;   // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591
	//	$mMysqli->error = 'fail'; // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591

		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		$result = $dbHandler->rawMultiQuery('SELECT `id` FROM `table`;SELECT `id` FROM `otherTable`;');
		
		self::assertIsArray($result);
		self::assertCount(2, $result);
		self::assertInstanceOf(iDbResult::class, $result[0]);
		self::assertInstanceOf(iDbResult::class, $result[1]);
		self::assertEquals(false, $result[0]->getInsertedId()); // see bug above, would be 123 otherwise
		self::assertEquals(false, $result[0]->getAffectedRecords()); // see bug above, would be 2 otherwise
		self::assertEquals(false, $result[1]->getInsertedId()); // see bug above, would be 123 otherwise
		self::assertEquals(false, $result[1]->getAffectedRecords()); // see bug above, would be 2 otherwise
    }
}
