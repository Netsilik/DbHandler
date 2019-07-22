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

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table`", []);

		self::assertEquals("SELECT `id` FROM `table`", $query);
		self::assertCount(1, $params);
		self::assertEmpty($params[0]);
	}
	
    public function test_whenEscapedCharacterPresent_thenEscapedCharacterIngored()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table` WHERE `id` = \%i", []);

		self::assertEquals("SELECT `id` FROM `table` WHERE `id` = \%i", $query);
		self::assertCount(1, $params);
		self::assertEmpty($params[0]);
	}
	
    public function test_whenSingleQuotedStringPresent_thenContentsOfQuotedStringIgnored()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table` WHERE `id` = '%i'", []);

		self::assertEquals("SELECT `id` FROM `table` WHERE `id` = '%i'", $query);
		self::assertCount(1, $params);
		self::assertEmpty($params[0]);
	}
	
    public function test_whenDoubleQuotedStringPresent_thenContentsOfQuotedStringIgnored()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', 'SELECT `id` FROM `table` WHERE `id` = "%i"', []);

		self::assertEquals('SELECT `id` FROM `table` WHERE `id` = "%i"', $query);
		self::assertCount(1, $params);
		self::assertEmpty($params[0]);
	}
	
    public function test_whenBacktickQuotedStringPresent_thenContentsOfQuotedStringIgnored()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` AS `x'%i` FROM `table`", []);

		self::assertEquals("SELECT `id` AS `x'%i` FROM `table`", $query);
		self::assertCount(1, $params);
		self::assertEmpty($params[0]);
	}
	
    public function test_whenInvalidParameterTypeIndicator_thenIndicatorIgnored()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table` WHERE `name` = %x", []);

		self::assertEquals("SELECT `id` FROM `table` WHERE `name` = %x", $query);
		self::assertCount(1, $params);
		self::assertEmpty($params[0]);
	}
	
    public function test_whenNamedAndIndexParametersBothUsed_thenInvalidArgumentExceptionThrown()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
		
		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage('Mixed named and indexed parameters not supported, please use one or the other');
		
		self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table` WHERE `name` = %i:foo AND `type` = %s", ['foo' => 123, 'bar']);
	}
	
    public function test_whenIndexAndNamedParametersBothUsed_thenInvalidArgumentExceptionThrown()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
		
		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage('Mixed indexed and named parameters not supported, please use one or the other');
		
		self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table` WHERE `name` = %i AND `type` = %s:foo", ['foo' => 'bar', 123]);
	}
	
    public function test_whenIndexedParameterAtEndOfString_thenProperlyRecognized()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table` WHERE `name` = %i", [123]);

		self::assertEquals("SELECT `id` FROM `table` WHERE `name` = ?", $query);
		self::assertCount(2, $params);
		self::assertEquals('i', $params[0]);
		self::assertEquals(123, $params[1]);
	}
	
    public function test_whenIndexedParameterInMiddleOfString_thenProperlyRecognized()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table` WHERE `name` = %i AND `id` > 10", [123]);

		self::assertEquals("SELECT `id` FROM `table` WHERE `name` = ? AND `id` > 10", $query);
		self::assertCount(2, $params);
		self::assertEquals('i', $params[0]);
		self::assertEquals(123, $params[1]);
	}
	
    public function test_whenIndexedParameterFollowedByWordCharacter_thenParameterIgnored()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table` WHERE `name` = %integer", []);

		self::assertEquals("SELECT `id` FROM `table` WHERE `name` = %integer", $query);
		self::assertCount(1, $params);
		self::assertEmpty($params[0]);
	}
	
    public function test_whenNotEnoughIndexParameterValuesProvided_thenInvalidArgumentExceptionThrown()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage('The number of parameters is not equal to the number of placeholders');
		
		self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table` WHERE `name` = %i AND `type` = %s", [123]);
	}
	
    public function test_whenIndexedFloatParameterGiven_thenDoubleReturnedInTokenString()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
		
		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table` WHERE `probability` = %f", [3.21]);
		
		self::assertEquals("SELECT `id` FROM `table` WHERE `probability` = ?", $query);
		self::assertCount(2, $params);
		self::assertEquals('d', $params[0]);
		self::assertEquals(3.21, $params[1]);
	}
	
    public function test_whenNamedFloatParameterGiven_thenDoubleReturnedInTokenString()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');
		
		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table` WHERE `probability` = %f:prob", ['prob' => 3.21]);
		
		self::assertEquals("SELECT `id` FROM `table` WHERE `probability` = ?", $query);
		self::assertCount(2, $params);
		self::assertEquals('d', $params[0]);
		self::assertEquals(3.21, $params[1]);
	}
	
    public function test_whenIndexedArrayParameterGivenOutsideAllAnyInSomeOperators_thenInvalidArgumentExceptionThrown()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage('Array parameter expansion is only supported in the ALL, ANY, IN and SOME operators');
		
		self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table` WHERE `name` = %i", [[123, 456, 789]]);
	}
	
    public function test_whenIndexedArrayParameterGivenInsideAllAnyInSomeOperators_thenArrayExpandedInListOfParametersOfSpecifiedType()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table` WHERE `type` IN (%i) AND `name` = %s", [[123, 456, 789], 'foo']);
		
		self::assertEquals("SELECT `id` FROM `table` WHERE `type` IN (?,?,?) AND `name` = ?", $query);
		self::assertCount(5, $params);
		self::assertEquals('iiis', $params[0]);
		self::assertEquals(123, $params[1]);
		self::assertEquals(456, $params[2]);
		self::assertEquals(789, $params[3]);
		self::assertEquals('foo', $params[4]);
	}
	
    public function test_whenNamedParameterNotFound_thenInvalidArgumentExceptionThrown()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage("Named parameter 'foo' not found");
		
		self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table` WHERE `name` = %i:foo", []);
	}
	
    public function test_whenNamedArrayParameterGivenOutsideAllAnyInSomeOperators_thenInvalidArgumentExceptionThrown()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage('Array parameter expansion is only supported in the ALL, ANY, IN and SOME operators');
		
		self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table` WHERE `name` = %i:foo", ['foo' => [123, 456, 789]]);
	}
	
    public function test_whenNamedArrayParameterGivenInsideAllAnyInSomeOperators_thenArrayExpandedInListOfParametersOfSpecifiedType()
    {
		$dbHandler = new DbHandler('localhost', 'root', 'secret');

		list($query, $params) = self::callInaccessibleMethod($dbHandler, '_preParse', "SELECT `id` FROM `table` WHERE `type` IN (%i:foo) AND `name` = %s:bar", ['foo' => [123, 456, 789], 'bar' => 'baz']);
		
		self::assertEquals("SELECT `id` FROM `table` WHERE `type` IN (?,?,?) AND `name` = ?", $query);
		self::assertCount(5, $params);
		self::assertEquals('iiis', $params[0]);
		self::assertEquals(123, $params[1]);
		self::assertEquals(456, $params[2]);
		self::assertEquals(789, $params[3]);
		self::assertEquals('baz', $params[4]);
	}
}
