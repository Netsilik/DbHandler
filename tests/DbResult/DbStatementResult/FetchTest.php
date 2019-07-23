<?php
namespace Tests\DbResult\AbstractDbResult;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use mysqli_stmt;
use Tests\BaseTestCase;
use Netsilik\DbHandler\DbResult\DbStatementResult;


class FetchTest extends BaseTestCase
{
    public function test_whenNoResultsSet_thenEmptyArrayReturned()
    {
    	$mMysqliStmt = self::createMock(mysqli_stmt::class);
    	$mMysqliStmt->method('result_metadata')->willReturn(false);
    	
		$dbResult = new DbStatementResult($mMysqliStmt, 0.0);
		$result = $dbResult->fetch();
		
		self::assertEmpty($result);
    }
    
    public function test_whenResultsSet_thenResultReturnedAsArray()
    {
    	$mMysqliStmt = self::createMock(mysqli_stmt::class);
    	$mMysqliStmt->method('result_metadata')->willReturn(false);
    	$mMysqliStmt->method('fetch')->willReturn(true, null);
		
		$dbResult = new DbStatementResult($mMysqliStmt, 0.0);
		
		self::setInaccessibleProperty($dbResult, '_result', ['id' => 1, 'name' => 'aaa']);
		
		$result = $dbResult->fetch();
		
		self::assertEquals([0 => ['id' => 1, 'name' => 'aaa']], $result);
    }
}
