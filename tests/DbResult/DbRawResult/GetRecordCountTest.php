<?php
namespace Tests\DbResult\DbStatementResult;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use stdClass;
use Tests\BaseTestCase;
use Netsilik\DbHandler\DbResult\DbRawResult;


class GetRecordCountTest extends BaseTestCase
{
    public function test_whenNumRowsSet_thenNumRowsReturned()
    {
    	$mResult = new stdClass();
    	$mResult->num_rows = 11;
    	
		$dbResult = new DbRawResult($mResult, 0.0);
		$result = $dbResult->getRecordCount();
		
		self::assertEquals(11, $result);
    }
    
    public function test_whenNumRowsNotSet_thenZeroReturned()
    {
    	$mResult = new stdClass();
    	
		$dbResult = new DbRawResult($mResult, 0.0);
		$result = $dbResult->getRecordCount();
		
		self::assertEquals(0, $result);
    }
    
}
