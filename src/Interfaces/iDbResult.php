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
	public function fetch() : array;
	
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
	 * @return mixed the value of the specified field or null if the field was not found
	 */
	public function fetchField(string $field = null, int $recordNum = 0);
	
	/**
	 * Fetch a record from a SELECT statement result
	 *
	 * @param int $recordNum return the num-th record from the set to get
	 *
	 * @return array|null An associative array containing all fields (columns) for specified record, or null if the records was not found
	 */
	public function fetchRecord(int $recordNum = 0) : ?array;
	
	/**
	 * Get the total number of records changed, deleted, or inserted by the last executed statement
	 *
	 * @return int number of records affected by this statement
	 */
	public function getAffectedRecords() : int;
	
	/**
	 * Get the number of fields (columns) in the result set
	 *
	 * @return int number of fields (columns) in the result set
	 */
	public function getFieldCount() : int;
	
	/**
	 * Get the primary for this inserted record
	 *
	 * @return int the value of the primary for this inserted record, or 0 if nothing was inserted
	 */
	public function getInsertedId() : int;
	
	/**
	 * Get the total number of records in this result set
	 *
	 * @return int number of records in this result set
	 */
	public function getRecordCount() : int;
	
	/**
	 * Get the query execution
	 *
	 * @return string time in seconds for this query
	 */
	public function getQueryTime() : string;
}
