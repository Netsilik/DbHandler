<?php
namespace Tests\DbResult\AbstractDbResult;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use Tests\BaseTestCase;
use Tests\Mocks\AbstractDbResultTestWrapper;


class FetchColumnTest extends BaseTestCase
{
    public function test_whenMethodCalledWithoutArgument_thenFirstColumnReturned()
    {
		$dbResult = new AbstractDbResultTestWrapper([
			0 => ['id' => 1, 'name' => 'aaa'],
			1 => ['id' => 2, 'name' => 'bbb'],
			2 => ['id' => 3, 'name' => 'ccc'],
		]);
	
		$result = $dbResult->fetchColumn();
		
		self::assertCount(3, $result);
		self::assertContains(1, $result);
		self::assertContains(2, $result);
		self::assertContains(3, $result);
    }
    
    public function test_whenMethodWithColumnName_thenNamedColumnReturned()
    {
		$dbResult = new AbstractDbResultTestWrapper([
			0 => ['id' => 1, 'name' => 'aaa'],
			1 => ['id' => 2, 'name' => 'bbb'],
			2 => ['id' => 3, 'name' => 'ccc'],
		]);
	
		$result = $dbResult->fetchColumn('name');
		
		self::assertCount(3, $result);
		self::assertContains('aaa', $result);
		self::assertContains('bbb', $result);
		self::assertContains('ccc', $result);
    }
}
