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


class ConnectTest extends BaseTestCase
{
    public function test_whenAllGoesWell_thenThisReturned()
    {
		$mMysqli_result = $this->createMock(mysqli_result::class);
		
		$mMysqli_stmt = $this->createMock(mysqli_stmt::class);
		$mMysqli_stmt->method('execute')->willReturn(true);
		$mMysqli_stmt->method('result_metadata')->willReturn($mMysqli_result);
		
		$mMysqli = $this->createMock(mysqli::class);
		$mMysqli->method('real_connect')->willReturn(true);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('prepare')->willReturn($mMysqli_stmt);
		
		FunctionOverwrites::setActive('mysqli_init', [$mMysqli]);
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
		$result = $dbHandler->connect();
		
        $this->assertEquals($dbHandler, $result);
    }
    
    public function test_whenMysqliInitDoesNotReturnMySQLiInstance_thenExceptionThrown()
    {
		FunctionOverwrites::setActive('mysqli_init', [null]);
		
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Could not initialize MySQLi instance');
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
		$dbHandler->connect();
    }
    
    public function test_whenNonExistingCACertificteFileGiven_thenInvalidArgumentExceptionThrown()
    {
    	$mMysqli = $this->createMock(mysqli::class);
		
		FunctionOverwrites::setActive('mysqli_init', [$mMysqli]);
		
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('CA-Certificate file could not be found');
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret', null, 'doesNotExist.pem');
		$dbHandler->connect();
    }
    
    public function test_whenValidCACertificteFileGiven_thenSslSetIsCalled()
    {
    	$mMysqli_result = $this->createMock(mysqli_result::class);
		
		$mMysqli_stmt = $this->createMock(mysqli_stmt::class);
		$mMysqli_stmt->method('execute')->willReturn(true);
		$mMysqli_stmt->method('result_metadata')->willReturn($mMysqli_result);
		
		$mMysqli = $this->createMock(mysqli::class);
		$mMysqli->method('real_connect')->willReturn(true);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('prepare')->willReturn($mMysqli_stmt);
		
		$mMysqli->expects($this->once())->method('ssl_set');
		
		FunctionOverwrites::setActive('mysqli_init', [$mMysqli]);
		
		$caCertFile = __DIR__ . '/../Mocks/ca_cert.pem';
		$dbHandler = new DbHandler('localhost', 'root', 'secret', null, $caCertFile);
		$dbHandler->connect();
    }
    
    public function test_whenCannotConnect_thenExceptionThrown()
    {
		$mMysqli_result = $this->createMock(mysqli_result::class);

		$mMysqli_stmt = $this->createMock(mysqli_stmt::class);
		$mMysqli_stmt->method('execute')->willReturn(true);
		$mMysqli_stmt->method('result_metadata')->willReturn($mMysqli_result);

		$mMysqli = $this->createMock(mysqli::class);
		$mMysqli->method('real_connect')->willReturn(false);
	//	$mMysqli->connect_error = 'fail'; // Cannot set properties on mock object, because of https://bugs.php.net/bug.php?id=63591

		FunctionOverwrites::setActive('mysqli_init', [$mMysqli]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Could not connect to DB-server: ' . null);

		$dbHandler = new DbHandler('localhost', 'root', 'secret');
		$dbHandler->connect();
    }
    
    public function test_whenDatabaseSpecified_thenSelectDbIsCalled()
    {
		$mMysqli_result = $this->createMock(mysqli_result::class);
		
		$mMysqli_stmt = $this->createMock(mysqli_stmt::class);
		$mMysqli_stmt->method('execute')->willReturn(true);
		$mMysqli_stmt->method('result_metadata')->willReturn($mMysqli_result);
		
		$mMysqli = $this->createMock(mysqli::class);
		$mMysqli->method('real_connect')->willReturn(true);
		$mMysqli->method('ping')->willReturn(true);
		$mMysqli->method('prepare')->willReturn($mMysqli_stmt);
		$mMysqli->method('select_db')->willReturn(true);
		
		$mMysqli->expects($this->once())->method('select_db');
		
		FunctionOverwrites::setActive('mysqli_init', [$mMysqli]);
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret', 'test');
		$result = $dbHandler->connect();
		
        $this->assertEquals($dbHandler, $result);
    }
}
