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


class RollbackTest extends BaseTestCase
{
    public function test_whenNoTransactionActive_thenNoticeTriggeredAndFalseReturned()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		$result = $dbHandler->rollback();

		self::assertErrorTriggered(E_USER_NOTICE, 'No transaction started');
		self::assertFalse($result);
	}

    public function test_whenTransactionActive_thenRollbackCalledAndAutocommitReEnabled()
    {
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('rollback')->willReturn(true);

		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		self::setInaccessibleProperty($dbHandler, '_inTransaction', true);


		$mMysqli->expects(self::once())->method('autocommit')->with(true);
		$mMysqli->expects(self::once())->method('rollback');

		$result = $dbHandler->rollback();

		$inTransaction = self::getInaccessibleProperty($dbHandler,'_inTransaction');
		
		self::assertTrue($result);
		self::assertFalse($inTransaction);
	}
}
