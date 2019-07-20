<?php
namespace Tests\Controllers\Login\ShowController;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use mysqli;
use Tests\BaseTestCase;
use Netsilik\DbHandler\DbHandler;


class CommitTest extends BaseTestCase
{
    public function test_whenNoTransactionActive_thenNoticeTriggeredAndFalseReturned()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		$result = $dbHandler->commit();

		self::assertErrorTriggered(E_USER_NOTICE, 'No transaction started');
		self::assertFalse($result);
	}

    public function test_whenTransactionActive_thenRollbackCalledAndAutocommitReEnabled()
    {
		$mMysqli = self::createMock(mysqli::class);
		$mMysqli->method('commit')->willReturn(true);

		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		self::setInaccessibleProperty($dbHandler, '_connection', $mMysqli);
		self::setInaccessibleProperty($dbHandler, '_inTransaction', true);


		$mMysqli->expects(self::once())->method('autocommit')->with(true);
		$mMysqli->expects(self::once())->method('commit');

		$result = $dbHandler->commit();

		$inTransaction = self::getInaccessibleProperty($dbHandler,'_inTransaction');
		
		self::assertTrue($result);
		self::assertFalse($inTransaction);
	}
}
