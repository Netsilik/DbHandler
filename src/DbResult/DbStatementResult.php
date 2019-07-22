<?php
namespace Netsilik\DbHandler\DbResult;

use mysqli_stmt;

/**
 * @package DbHandler
 * @copyright (c) 2011-2019 Netsilik (http://netsilik.nl)
 * @license EUPL-1.1 (European Union Public Licence, v1.1)
 */


/**
 * Result object, returned by the DbHandler whenever a valid query is executed
 */
class DbStatementResult extends AbstractDbResult
{
	
	/**
	 * @var \mysqli_stmt $_statement The MySQLi statement instance
	 */
	protected $_statement = null;
	
	/**
	 * @param \mysqli_stmt $statement Statement the statement for this result set
	 * @param int $queryTime the time in seconds the statement took to execute
	 */
	public function __construct (mysqli_stmt $statement, $queryTime)
	{
		$this->_statement = $statement;
		$this->_queryTime = $queryTime;
		
		if ( false !== ($metaData = $statement->result_metadata()) ) {
			$statement->store_result();
			
			$params = [];
			while ($field = $metaData->fetch_field()) {
				$params[] = &$this->_result[$field->name];
			}
			call_user_func_array(array($this->_statement, 'bind_result'), $params);
			$metaData->close();
		}
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function fetch()
	{
		if (is_null($this->_result)) { // No result data available
			return false;
		}
		
		$records = [];
		while ($this->_statement->fetch()) {
			$column = [];
			foreach($this->_result as $name => $value) {
				$column[$name] = $value;
			}
			$records[] = $column;
		}
		$this->_statement->data_seek(0);
		return $records;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getAffectedRecords()
	{
		return $this->_statement->affected_rows;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getFieldCount()
	{
		return $this->_statement->field_count;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getInsertedId()
	{
		return $this->_statement->insert_id;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getRecordCount()
	{
		return $this->_statement->num_rows;
	}
	
	/**
	 * Destructor
	 */
	public function __destruct ()
	{
		$this->_statement->close();
	}
}
