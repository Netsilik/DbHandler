<?php
namespace Tests\Mocks;

/**
 * @package DbHandler
 * @copyright (c) 2011-2019 Netsilik (http://netsilik.nl)
 * @license EUPL-1.1 (European Union Public Licence, v1.1)
 */

use Netsilik\DbHandler\DbResult\AbstractDbResult;

/**
 * Result object, returned by the DbHandler whenever a valid query is executed
 */
class AbstractDbResultTestWrapper extends AbstractDbResult
{

	protected $_records;
	
	public function __construct(array $records = [])
	{
		$this->_records = $records;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function fetch() : array
	{
		return $this->_records;
	}
	
	public function getAffectedRecords() : int
	{
		return -1; // Not needed for testing
	}
	
	public function getFieldCount() : int
	{
		return -1; // Not needed for testing
	}
	
	public function getInsertedId() : int
	{
		return -1; // Not needed for testing
	}
	
	public function getRecordCount() : int
	{
		return count($this->_records);
	}
}
