<?php
namespace Netsilik\DbHandler\Interfaces;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2019 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use mysqli;


interface iDbHandler
{
	/**
	 * Connect to the Database Server
	 *
	 * @return iDbHandler $this
	 * @throws \Exception
	 * @throws \InvalidArgumentException
	 */
	public function connect() : iDbHandler;
	
	/**
	 * Check whether we have a open connection
	 *
	 * @return bool
	 */
	public function isConnected() : bool;
	
	/**
	 * @return mysqli Mysqli instance
	 */
	public function getConnection() : mysqli;
	
	/**
	 * Select a database to use on current connection
	 *
	 * @param string $dbName name of the database to select
	 *
	 * @return true on success, false otherwise
	 * @throws \InvalidArgumentException
	 */
	public function selectDb(string $dbName) : bool;
	
	/**
	 * Execute a prepared query
	 *
	 * @param string $query        The query with zero or more parameter marker characters at the appropriate positions. Parameter markers are
	 *                             defined as a literal % followed by either:
	 *                             i : corresponding variable will be interpreted as an integer
	 *                             f : corresponding variable will be interpreted as a float
	 *                             s : corresponding variable will be interpreted as a string
	 *                             b : corresponding variable will be interpreted as a blob and should be sent in packets (but this is not yet
	 *                             supported by MySQL)
	 * @param array $params        An optional array, with values matching the parameter markers in $query
	 * @param int $failRetryCount The number of times failed queries should be retried, for recoverable error numbers
	 *
	 * @return \Netsilik\DbHandler\Interfaces\iDbResult An iDbResult instance with the result of the executed query
	 * @throws \Exception
	 * @throws \InvalidArgumentException
	 */
	public function query(string $query, array $params = [], int $failRetryCount = 3) : iDbResult;
	
	/**
	 * Execute a query, as is. Please pay attention to escaping any user provides values
	 *
	 * @param string $query The query to execute
	 *
	 * @return \Netsilik\DbHandler\Interfaces\iDbResult A iDbResult implementation
	 * @throws \Exception
	 */
	public function rawQuery(string $query) : iDbResult;
	
	/**
	 * Execute multiple query, as they appear in the query string, on after another.
	 * Please be exceptionally careful with this method: it is a power user function. Any mistake with user provided values will put a
	 * very powerful interface in the hands of the would-be attacker.
	 *
	 * @param string $query The query to execute
	 *
	 * @return array An indexed array of \Netsilik\DbHandler\Interfaces\iDbResult result sets
	 * @throws \Exception
	 */
	public function rawMultiQuery(string $query) : array;
	
	/**
	 * Escapes special characters in a string for use in an SQL statement, taking into account the current charset of the connection.
	 *
	 * @param string $value
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function escape(string $value) : string;
	
	/**
	 * Start a transaction ('advanced' features such as WITH CONSISTENT SNAPSHOT are not supported)
	 *
	 * @return bool true on success, false on failure
	 */
	public function startTransaction() : bool;
	
	/**
	 * Rollback a transaction
	 *
	 * @return bool true on success, false on failure
	 */
	public function rollback() : bool;
	
	/**
	 * Commit a transaction
	 *
	 * @return bool true on success, false on failure
	 */
	public function commit() : bool;
	
	/**
	 * Close the connection
	 *
	 * @return void
	 */
	public function close() : void;
	
}
