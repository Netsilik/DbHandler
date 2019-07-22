<?php
namespace Tests\DbHandler;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use Exception;
use mysqli;
use mysqli_stmt;
use Tests\BaseTestCase;
use Netsilik\DbHandler\DbHandler;
use Netsilik\DbHandler\Interfaces\iDbResult;


class QueryTest extends BaseTestCase
{
    public function test_whenMethodCalled_thenIDbResultInstanceReturned()
    {
		$mMysqliStmt = self::createMock(mysqli_stmt::class);
		$mMysqliStmt->method('execute')->willReturn(true);
		$mMysqliStmt->method('result_metadata')->willReturn(false);
		
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('prepare')->willReturn($mMysqliStmt);
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		$result = $dbHandler->query('SELECT `id` FROM `table`');
		
		self::assertInstanceOf(iDbResult::class, $result);
    }
    
    public function test_whenQueryPreparationFailed_thenExceptionThrown()
    {
	//	$mMysqliStmt = self::createMock(mysqli_stmt::class);
	//	$mMysqliStmt->errno = 123;    // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591
	//	$mMysqliStmt->error = 'fail'; // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591
		
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('prepare')->willReturn(false);
	
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		self::expectException(Exception::class);
		self::expectExceptionMessage('Query preparation failed: ' . null);
		
		$dbHandler->query('SELECT `id` FROM `table`');
    }
    
    public function test_whenParameterBindingFailed_thenExceptionThrown()
    {
		$mMysqliStmt = self::createMock(mysqli_stmt::class);
		$mMysqliStmt->method('bind_param')->willReturn(false);
	//	$mMysqliStmt->errno = 123;    // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591
	//	$mMysqliStmt->error = 'fail'; // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591
		
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('prepare')->willReturn($mMysqliStmt);
	
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		self::expectException(Exception::class);
		self::expectExceptionMessage('Parameter binding failed: ' . null);
		
		$dbHandler->query('SELECT `id` FROM `table` WHERE `name` = %s', ['foo']);
    }
    
    public function test_whenQueryExecutionFailedAndNoMoreRetries_thenExceptionThrown()
    {
		$mMysqliStmt = self::createMock(mysqli_stmt::class);
	//	$mMysqliStmt->errno = 123;    // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591
	//	$mMysqliStmt->error = 'fail'; // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591
		
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('prepare')->willReturn($mMysqliStmt);
	
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		self::expectException(Exception::class);
		self::expectExceptionMessage('Query execution failed: ' . null);
		
		$dbHandler->query('SELECT `id` FROM `table`', [], 0);
    }
}
