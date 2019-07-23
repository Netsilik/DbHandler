<?php
namespace Tests\DbResult\DbRawResult;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use stdClass;
use Tests\BaseTestCase;
use Netsilik\DbHandler\DbResult\DbRawResult;


class GetAffectedRecordsTest extends BaseTestCase
{
    public function test_whenAffectedRowsSet_thenAffectedRowsReturned()
    {
    	$mResult = new stdClass();
    	$mResult->affected_rows = 11;
    	
		$dbResult = new DbRawResult($mResult, 0.0);
		$result = $dbResult->getAffectedRecords();
		
		self::assertEquals(11, $result);
    }
    
    public function test_whenAffectedRowsNotSet_thenNumRowsReturned()
    {
    	$mResult = new stdClass();
    	$mResult->num_rows = 13;
    	
		$dbResult = new DbRawResult($mResult, 0.0);
		$result = $dbResult->getAffectedRecords();
		
		self::assertEquals(13, $result);
    }
}
