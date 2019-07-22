<?php
namespace Tests\DbHandler;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use InvalidArgumentException;
use mysqli;
use Tests\BaseTestCase;
use Netsilik\DbHandler\DbHandler;


class SelectDbTest extends BaseTestCase
{
    public function test_whenInvalidDbSpecified_thenInvalidArgumentExceptionThrown()
    {
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('select_db')->willReturn(false);
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		$mMysqli->expects(self::once())->method('select_db');
		
		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage("Could not select database 'foo'");
		
		$dbHandler->selectDb('foo');
    }
    
    public function test_whenValidDbSpecified_thenTrueReturned()
    {
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('select_db')->willReturn(true);
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		$mMysqli->expects(self::once())->method('select_db');
		
		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		$result = $dbHandler->selectDb('foo');
		
		self::assertTrue($result);
    }
}
