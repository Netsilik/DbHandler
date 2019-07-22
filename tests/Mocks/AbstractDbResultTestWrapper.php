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
	public function fetch()
	{
		return $this->_records;
	}
	
	public function getAffectedRecords()
	{
		// TODO: Implement getAffectedRecords() method.
	}
	
	public function getFieldCount()
	{
		// Not needed for testing
	}
	
	public function getInsertedId()
	{
		// Not needed for testing
	}
	
	public function getRecordCount()
	{
		return count($this->_records);
	}
}
