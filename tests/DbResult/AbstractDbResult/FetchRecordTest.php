<?php
namespace Tests\DbResult\AbstractDbResult;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use Tests\BaseTestCase;
use Tests\Mocks\AbstractDbResultTestWrapper;


class FetchRecordTest extends BaseTestCase
{
    public function test_whenMethodCalledWithoutArgument_thenFirstRecordReturned()
    {
		$dbResult = new AbstractDbResultTestWrapper([
			0 => ['id' => 1, 'name' => 'aaa'],
			1 => ['id' => 2, 'name' => 'bbb'],
			2 => ['id' => 3, 'name' => 'ccc'],
		]);
	
		$result = $dbResult->fetchRecord();
		
		self::assertEquals(['id' => 1, 'name' => 'aaa'], $result);
    }
    
    public function test_whenRecordNumberGiven_thenSpecifiedRecordReturned()
    {
		$dbResult = new AbstractDbResultTestWrapper([
			0 => ['id' => 1, 'name' => 'aaa'],
			1 => ['id' => 2, 'name' => 'bbb'],
			2 => ['id' => 3, 'name' => 'ccc'],
		]);
	
		$result = $dbResult->fetchRecord(2);
		
		self::assertEquals(['id' => 3, 'name' => 'ccc'], $result);
    }
    
    public function test_whenUnknownRecordNumberGiven_thenEmptyArrayReturned()
    {
		$dbResult = new AbstractDbResultTestWrapper([
			0 => ['id' => 1, 'name' => 'aaa'],
			1 => ['id' => 2, 'name' => 'bbb'],
			2 => ['id' => 3, 'name' => 'ccc'],
		]);
	
		$result = $dbResult->fetchRecord(100);
		
		self::assertEmpty($result);
    }
    
}
