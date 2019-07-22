<?php
namespace Netsilik\DbHandler\DbResult;

/**
 * @package DbHandler
 * @copyright (c) 2011-2019 Netsilik (http://netsilik.nl)
 * @license EUPL-1.1 (European Union Public Licence, v1.1)
 */

use mysqli_result;

/**
 * Result object, returned by the DbHandler whenever a valid query is executed
 */
class DbRawResult extends AbstractDbResult
{

	protected $_records = null;
	
	/**
	 * @param object $result Either an instance of mysqli_result or an instance of StdClass
	 * @param int $queryTime the time in seconds the statement took to execute
	 */
	public function __construct ($result, $queryTime)
	{
		$this->_result = $result;
		
		$this->_queryTime = $queryTime;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function fetch()
	{
		if ($this->_result instanceof mysqli_result) {
			if (null === $this->_records) {
				$this->_records = array();
				
				while ($record = $this->_result->fetch_assoc()) {
					$this->_records[] = $record;
				}
			
			}			
			return $this->_records;
		}
		return false;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getAffectedRecords()
	{
		if (isset($this->_result->affected_rows)) {
			return $this->_result->affected_rows;
		}
		return $this->_result->num_rows;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getFieldCount()
	{
		if (isset($this->_result->field_count)) {
			return $this->_result->field_count;
		}
		return 0;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getInsertedId()
	{
		if (isset($this->_result->insert_id)) {
			return $this->_result->insert_id;
		}
		return 0;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getRecordCount()
	{
		if (isset($this->_result->num_rows)) {
			return $this->_result->num_rows;
		}
		return 0;
	}
	
	/**
	 * Destructor
	 */
	public function __destruct()
	{
		if ($this->_result instanceof mysqli_result) {
			$this->_result->free();
		}
		$this->_result = null;
	}
}
