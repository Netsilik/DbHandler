<?php
namespace Tests\DbResult\AbstractDbResult;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use stdClass;
use mysqli_stmt;
use mysqli_result;
use Tests\BaseTestCase;
use Netsilik\DbHandler\DbResult\DbStatementResult;


class ConstructTest extends BaseTestCase
{
    public function test_whenConstructorCalledWithMetaDataAvailable_thenResultParameterSet()
    {
    	$firstFetchField = new stdClass();
    	$firstFetchField->name = 'id';
    	$secondFetchField = new stdClass();
    	$secondFetchField->name = 'name';

    	$mMysqliResult = self::createMock(mysqli_result::class);
    	$mMysqliResult->method('fetch_field')->willReturn($firstFetchField, $secondFetchField, false);

    	$mMysqliStmt = self::createMock(mysqli_stmt::class);
    	$mMysqliStmt->method('result_metadata')->willReturn($mMysqliResult);

		$dbResult = new DbStatementResult($mMysqliStmt, 0.0);
		$result = $dbResult->fetch();

		self::assertEmpty($result);
    }
}
