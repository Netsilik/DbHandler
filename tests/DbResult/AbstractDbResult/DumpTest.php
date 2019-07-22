<?php
namespace Tests\DbResult\AbstractDbResult;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use stdClass;
use Tests\BaseTestCase;
use Tests\Mocks\AbstractDbResultTestWrapper;


class DumpTest extends BaseTestCase
{
    public function test_whenResultIsNull_thenQueryOkOutput()
    {
		$dbResult = new AbstractDbResultTestWrapper();
		self::setInaccessibleProperty($dbResult, '_queryTime', 1234.567);
	
		self::expectOutputRegex('/Query OK/');
		
		$dbResult->dump();
    }
    
    public function test_whenResultIsEmpty_thenEmptySetOutput()
	{
		$dbResult = new AbstractDbResultTestWrapper([]);
		self::setInaccessibleProperty($dbResult, '_queryTime', 1234.567);
		self::setInaccessibleProperty($dbResult, '_result', new stdClass());
		
		self::expectOutputRegex('/empty set/');
		
		$dbResult->dump();
	}
    
    public function test_whenResultNotEmpty_thenTableOutput()
	{
		$dbResult = new AbstractDbResultTestWrapper([
			0 => ['id' => 1, 'name' => 'aaa'],
			1 => ['id' => 2, 'name' => 'bbb'],
			2 => ['id' => 3, 'name' => 'ccc'],
		]);
		self::setInaccessibleProperty($dbResult, '_queryTime', 1234.567);
		self::setInaccessibleProperty($dbResult, '_result', new stdClass());
		
		self::expectOutputRegex('/<table[^>]>/');
		self::expectOutputRegex('/<\/table>/');
		
		$dbResult->dump();
	}
}
