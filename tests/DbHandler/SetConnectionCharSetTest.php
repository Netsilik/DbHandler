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


class SetConnectionCharSetTest extends BaseTestCase
{
    public function test_whenMethodCalled_thenSetCharsetCalledAndSelfReturned()
    {
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->expects(self::once())->method('set_charset');
	
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
	
	
		$result = $dbHandler->setConnectionCharSet('utf8-bin');
		
		self::assertEquals($dbHandler, $result);
	}
}
