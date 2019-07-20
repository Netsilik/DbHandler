<?php
namespace Tests\DbHandler;

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


class StartTransactionTest extends BaseTestCase
{
    public function test_whenTransactionAlreadyStarted_thenNoticeTriggeredAndBeginTransactionCalled()
    {
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('begin_transaction')->willReturn(true);
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		self::setInaccessibleProperty($dbHandler, '_inTransaction', true);
		
		$mMysqli->expects(self::once())->method('begin_transaction');
		
		$dbHandler->startTransaction();
		
		$inTransaction = self::getInaccessibleProperty($dbHandler,'_inTransaction');
		
		self::assertErrorTriggered(E_USER_NOTICE, 'Implicit commit for previous transaction');
		self::assertTrue($inTransaction);
	}
	
    public function test_whenNewTransactionInitiated_thenAutocommitDisabledAndBeginTransactionCalled()
    {
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('begin_transaction')->willReturn(false);
		$mMysqli->expects(self::once())->method('begin_transaction');
		
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
	
		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		
		$mMysqli->expects(self::once())->method('begin_transaction');
	
		$dbHandler->startTransaction();
		
		$inTransaction = self::getInaccessibleProperty($dbHandler,'_inTransaction');
		
		self::assertTrue($inTransaction);
	}
}