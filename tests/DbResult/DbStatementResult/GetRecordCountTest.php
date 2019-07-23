<?php
namespace Tests\DbResult\AbstractDbResult;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use stdClass;
use mysqli_stmt;
use Tests\BaseTestCase;
use Netsilik\DbHandler\DbResult\DbStatementResult;


class GetRecordCountTest extends BaseTestCase
{
    public function test_whenMethodCalled_thenNumRowsReturned()
    {
    	$mMysqliStmt = self::createMock(mysqli_stmt::class);
    	$mMysqliStmt->method('result_metadata')->willReturn(false);

		$dbResult = new DbStatementResult($mMysqliStmt, 0.0);

		$mStatement = new stdClass();
		$mStatement->num_rows = 123;
		self::setInaccessibleProperty($dbResult, '_statement', $mStatement); // make sure we have a value (because: https://bugs.php.net/bug.php?id=63591)

		$result = $dbResult->getRecordCount();
	
		self::assertEquals(123, $result);
		
		self::setInaccessibleProperty($dbResult, '_statement', $mMysqliStmt); // restore valid Mock
	}
    
}
