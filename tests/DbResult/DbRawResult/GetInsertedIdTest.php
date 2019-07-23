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


class GetInsertedIdTest extends BaseTestCase
{
    public function test_whenInsertedIdSet_thenInsertedIdReturned()
    {
    	$mResult = new stdClass();
    	$mResult->insert_id = 11;
    	
		$dbResult = new DbRawResult($mResult, 0.0);
		$result = $dbResult->getInsertedId();
		
		self::assertEquals(11, $result);
    }
    
    public function test_whenInsertedIdNotSet_thenZeroReturned()
    {
    	$mResult = new stdClass();
    	
		$dbResult = new DbRawResult($mResult, 0.0);
		$result = $dbResult->getInsertedId();
		
		self::assertEquals(0, $result);
    }
    
}
