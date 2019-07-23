<?php
namespace Tests\DbResult\AbstractDbResult;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use mysqli_result;
use Tests\BaseTestCase;
use Netsilik\DbHandler\DbResult\DbRawResult;


class FetchTest extends BaseTestCase
{
    public function test_whenStdClassGivenToConstructor_thenEmptyArrayReturned()
    {
		$dbResult = new DbRawResult(new \stdClass(), 0.0);
		$result = $dbResult->fetch();
		
		self::assertEmpty($result);
    }
    
    public function test_whenMysqliResultGivenToConstructor_thenDataReturned()
    {
    	$mMysqliResult = self::createMock(mysqli_result::class);
		$mMysqliResult->method('fetch_assoc')->willReturn(['id' => 1, 'name' => 'aaa'], null);
		
		$dbResult = new DbRawResult($mMysqliResult, 0.0);
		$result = $dbResult->fetch();
		
		self::assertContains(['id' => 1, 'name' => 'aaa'], $result);
    }
}
