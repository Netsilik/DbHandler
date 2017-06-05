<?php
namespace Netsilik\DbHandler\DbResult;

/**
 * @package DbHandler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license EUPL-1.1 (European Union Public Licence, v1.1)
 */

interface iDbResult {
	
	/**
	 * Fetch the results for an SELECT statement
	 * @return array an associative array per record, containing all fields (columns)
	 */
	public function fetch();
	
	/**
	 * Fetch a column from a SELECT statement result
	 * @param int $column optional name of the column to fetch
	 * @return array an indexed array containing all fields for specified column or the first column if none specified
	 */
	public function fetchColumn($column = null);
	
	/**
	 * Fetch a field from a SELECT statement result
	 * @param int $field name of the column of the first record to fetch
	 * @return scalar the value of the specified field
	 */
	public function fetchField($field = null, $recordNum = 0);
	
	/**
	 * Fetch a record from a SELECT statement result
	 * @param int $recordNum return the num-th record from the set to get
	 * @return array an associative array containing all fields (columns) for specified record
	 */
	public function fetchRecord($recordNum = 0);
	
	/**
	 * Get the total number of records changed, deleted, or inserted by the last executed statement 
	 * @return int number of records affected by this statement
	 */
	public function getAffectedRecords();
	
	/**
	 * Get the number of fields (columns) in the result set
	 * @return int number of fields (columns) in the result set
	 */
	public function getFieldCount();
	
	/**
	 * Get the primary for this inserted record
	 * @return int the value of the primary for this inserted record, or 0 if nothing was inserted
	 */
	public function getInsertedId();
	
	/**
	 * Get the total number of records in this result set
	 * @return int number of records in this result set
	 */
	public function getRecordCount();
}