<?php
namespace Tests\DbHandler;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use Tests\BaseTestCase;
use Netsilik\DbHandler\DbHandler;


class ErrorNoIsRetryableTest extends BaseTestCase
{
    public function test_whenNonRetryableErrorNo_thenFalseReturned()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		$result = self::callInaccessibleMethod($dbHandler, '_errorNoIsRetryable', 1000);

		self::assertFalse($result);
	}
	
    public function test_whenRetryableImmediatelyErrorNo_thenTrueReturned()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		$result = self::callInaccessibleMethod($dbHandler, '_errorNoIsRetryable', 2006);

		self::assertTrue($result);
	}
	
    public function test_whenRetryableAfterSleepErrorNo_thenTrueReturned()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		$result = self::callInaccessibleMethod($dbHandler, '_errorNoIsRetryable', 1205);

		self::assertTrue($result);
	}
}
