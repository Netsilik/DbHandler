<?php
namespace Netsilik\DbHandler\Interfaces;

/**
 * @package       DbHandler
 * @copyright (c) 2011-2019 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

interface iDbResult
{
	
	/**
	 * Fetch the results for an SELECT statement
	 *
	 * @return array An associative array per record, containing all fields (columns)
	 */
	public function fetch();
	
	/**
	 * Fetch a column from a SELECT statement result
	 *
	 * @param int $column The Optional name of the column to fetch
	 *
	 * @return array An indexed array containing all fields for specified column or the first column if none specified
	 */
	public function fetchColumn(string $column = null) : array;
	
	/**
	 * Fetch a field from a SELECT statement result
	 *
	 * @param int $field     name of the column of the first record to fetch
	 * @param int $recordNum The index number of the record to fetch the field from
	 *
	 * @return mixed the value of the specified field
	 */
	public function fetchField(string $field = null, int $recordNum = 0);
	
	/**
	 * Fetch a record from a SELECT statement result
	 *
	 * @param int $recordNum return the num-th record from the set to get
	 *
	 * @return array an associative array containing all fields (columns) for specified record
	 */
	public function fetchRecord(int $recordNum = 0) : array;
	
	/**
	 * Get the total number of records changed, deleted, or inserted by the last executed statement
	 *
	 * @return int number of records affected by this statement
	 */
	public function getAffectedRecords();
	
	/**
	 * Get the number of fields (columns) in the result set
	 *
	 * @return int number of fields (columns) in the result set
	 */
	public function getFieldCount();
	
	/**
	 * Get the primary for this inserted record
	 *
	 * @return int the value of the primary for this inserted record, or 0 if nothing was inserted
	 */
	public function getInsertedId();
	
	/**
	 * Get the total number of records in this result set
	 *
	 * @return int number of records in this result set
	 */
	public function getRecordCount();
	
	/**
	 * Get the query execution
	 *
	 * @return int time in seconds for this query
	 */
	public function getQueryTime() : string;
}
