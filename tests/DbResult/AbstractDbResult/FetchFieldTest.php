<?php
namespace Tests\DbResult\AbstractDbResult;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use Tests\BaseTestCase;
use Tests\Mocks\AbstractDbResultTestWrapper;


class FetchFieldTest extends BaseTestCase
{
    public function test_whenMethodCalledWithoutArgument_thenFirstFieldFromFirstRecordReturned()
    {
		$dbResult = new AbstractDbResultTestWrapper([
			0 => ['id' => 1, 'name' => 'aaa'],
			1 => ['id' => 2, 'name' => 'bbb'],
			2 => ['id' => 3, 'name' => 'ccc'],
		]);
	
		$result = $dbResult->fetchField();
		
		self::assertEquals(1, $result);
    }
    
    public function test_whenUnknownFieldRequested_thenNullReturned()
    {
		$dbResult = new AbstractDbResultTestWrapper([
			0 => ['id' => 1, 'name' => 'aaa'],
			1 => ['id' => 2, 'name' => 'bbb'],
			2 => ['id' => 3, 'name' => 'ccc'],
		]);
	
		$result = $dbResult->fetchField('xxxxxxx');
		
		self::assertNull($result);
    }
    
    public function test_whenMFieldNameGiven_thenNamedFieldFromFirstRecordReturned()
    {
		$dbResult = new AbstractDbResultTestWrapper([
			0 => ['id' => 1, 'name' => 'aaa'],
			1 => ['id' => 2, 'name' => 'bbb'],
			2 => ['id' => 3, 'name' => 'ccc'],
		]);
	
		$result = $dbResult->fetchField('name');
		
		self::assertEquals('aaa', $result);
    }
    
    public function test_whenFieldNameAndRecordGiven_thenNamedFieldFromGivenRecordReturned()
    {
		$dbResult = new AbstractDbResultTestWrapper([
			0 => ['id' => 1, 'name' => 'aaa'],
			1 => ['id' => 2, 'name' => 'bbb'],
			2 => ['id' => 3, 'name' => 'ccc'],
		]);
	
		$result = $dbResult->fetchField('name', 2);
		
		self::assertEquals('ccc', $result);
    }
}
