<?php
namespace Tests\Controllers\Login\ShowController;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use mysqli;
use Exception;
use mysqli_stmt;
use mysqli_result;
use Tests\BaseTestCase;
use InvalidArgumentException;
use Netsilik\DbHandler\DbHandler;
use Tests\Mocks\FunctionOverwrites;


class EscapeTest extends BaseTestCase
{
    public function test_whenMethodCalled_thenEscapedStringReturned()
    {
		$mMysqli = $this->createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('escape_string')->willReturn('foo');
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		$mMysqli->expects($this->once())->method('escape_string');
		
		$this->setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		$dbHandler->escape('foo');
    }
}