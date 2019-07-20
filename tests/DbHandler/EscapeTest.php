<?php
namespace Tests\DbHandler;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use mysqli;
use Tests\BaseTestCase;
use Netsilik\DbHandler\DbHandler;


class EscapeTest extends BaseTestCase
{
    public function test_whenMethodCalled_thenEscapedStringReturned()
    {
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('escape_string')->willReturn('foo');
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		$mMysqli->expects(self::once())->method('escape_string');
		
		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		$dbHandler->escape('foo');
    }
}