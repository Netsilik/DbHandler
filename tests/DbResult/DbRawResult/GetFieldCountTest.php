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


class GetFieldCountTest extends BaseTestCase
{
    public function test_whenFieldCountSet_thenFieldCountReturned()
    {
    	$mResult = new stdClass();
    	$mResult->field_count = 11;
    	
		$dbResult = new DbRawResult($mResult, 0.0);
		$result = $dbResult->getFieldCount();
		
		self::assertEquals(11, $result);
    }
    
    public function test_whenFieldCountNotSet_thenZeroReturned()
    {
    	$mResult = new stdClass();
    	
		$dbResult = new DbRawResult($mResult, 0.0);
		$result = $dbResult->getFieldCount();
		
		self::assertEquals(0, $result);
    }
    
}
