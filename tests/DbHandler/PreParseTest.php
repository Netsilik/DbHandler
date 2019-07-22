<?php
namespace Tests\DbHandler;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use Tests\BaseTestCase;
use InvalidArgumentException;
use Netsilik\DbHandler\DbHandler;


class PreParseTest extends BaseTestCase
{
    public function test_whenSimpleQuery_thenUnmodifiedQuertWithNoParametersReturned()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM table", []);

		self::assertEquals("SELECT `id` FROM table", $query);
		self::assertCount(1, $params);
		self::assertEmpty($params[0]);
	}
	
    public function test_whenEscapedCharacterPresent_thenEscapedCharacterIngored()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM table WHERE `id` = \%i", []);

		self::assertEquals("SELECT `id` FROM table WHERE `id` = \%i", $query);
		self::assertCount(1, $params);
		self::assertEmpty($params[0]);
	}
	
    public function test_whenSingleQuotedStringPresent_thenContentsOfQuotedStringIgnored()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM table WHERE `id` = '%i'", []);

		self::assertEquals("SELECT `id` FROM table WHERE `id` = '%i'", $query);
		self::assertCount(1, $params);
		self::assertEmpty($params[0]);
	}
	
    public function test_whenDoubleQuotedStringPresent_thenContentsOfQuotedStringIgnored()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', 'SELECT `id` FROM table WHERE `id` = "%i"', []);

		self::assertEquals('SELECT `id` FROM table WHERE `id` = "%i"', $query);
		self::assertCount(1, $params);
		self::assertEmpty($params[0]);
	}
	
    public function test_whenBacktickQuotedStringPresent_thenContentsOfQuotedStringIgnored()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` AS `x'%i` FROM table", []);

		self::assertEquals("SELECT `id` AS `x'%i` FROM table", $query);
		self::assertCount(1, $params);
		self::assertEmpty($params[0]);
	}
	
    public function test_whenInvalidParameterTypeIndicator_thenIndicatorIgnored()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM table WHERE `name` = %x", []);

		self::assertEquals("SELECT `id` FROM table WHERE `name` = %x", $query);
		self::assertCount(1, $params);
		self::assertEmpty($params[0]);
	}
	
    public function test_whenNamedAndIndexParametersBothUsed_thenInvalidArgumentExceptionThrown()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
		
		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage('Mixed named and indexed parameters not supported, please use one or the other');
		
		self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM table WHERE `name` = %i:foo AND `type` = %s", ['foo' => 123, 'bar']);
	}
	
    public function test_whenIndexAndNamedParametersBothUsed_thenInvalidArgumentExceptionThrown()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
		
		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage('Mixed indexed and named parameters not supported, please use one or the other');
		
		self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM table WHERE `name` = %i AND `type` = %s:foo", ['foo' => 'bar', 123]);
	}
	
    public function test_whenIndexedParameterAtEndOfString_thenProperlyRecognized()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM table WHERE `name` = %i", [123]);

		self::assertEquals("SELECT `id` FROM table WHERE `name` = ?", $query);
		self::assertCount(2, $params);
		self::assertEquals('i', $params[0]);
		self::assertEquals(123, $params[1]);
	}
	
    public function test_whenIndexedParameterInMiddleOfString_thenProperlyRecognized()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM table WHERE `name` = %i AND `id` > 10", [123]);

		self::assertEquals("SELECT `id` FROM table WHERE `name` = ? AND `id` > 10", $query);
		self::assertCount(2, $params);
		self::assertEquals('i', $params[0]);
		self::assertEquals(123, $params[1]);
	}
	
    public function test_whenIndexedParameterFollowedByWordCharacter_thenParameterIgnored()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM table WHERE `name` = %integer", []);

		self::assertEquals("SELECT `id` FROM table WHERE `name` = %integer", $query);
		self::assertCount(1, $params);
		self::assertEmpty($params[0]);
	}
	
    public function test_whenNotEnoughIndexParameterValuesProvided_thenInvalidArgumentExceptionThrown()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage('The number of parameters is not equal to the number of placeholders');
		
		self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM table WHERE `name` = %i AND `type` = %s", [123]);
	}
	
	// check that f is converted to d
}
