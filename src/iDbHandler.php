<?php
namespace Netsilik\DbHandler;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use mysqli;
use Netsilik\DbHandler\DbResult\DbRawResult;
use Netsilik\DbHandler\DbResult\DbStatementResult;

interface iDbHandler
{
	/**
	 * Connect to the Database Server
	 *
	 * @return iDbHandler $this
	 * @throws \Exception
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
	 * @return \Netsilik\DbHandler\DbResult\DbStatementResult object holding the result of the executed query
	 * @throws \Exception
	 * @throws \InvalidArgumentException
	 */
	public function query(string $query, array $params = [], int $failRetryCount = 3) : DbStatementResult;
	
	/**
	 * Execute a query, as is. Please pay attention to escaping any user provides values
	 *
	 * @param string $query The query to execute
	 * @param bool $multiple Indicate if the $query string contains multiple queries that should be executed
	 *
	 * @return \Netsilik\DbHandler\DbResult\DbRawResult A DbRawResult
	 * @throws \Exception
	 */
	public function rawQuery(string $query) : DbRawResult;
	
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
	 * @param bool $silent If false, a E_USER_NOTICE is emitted when no transaction has been started
	 *
	 * @return bool true on success, false on failure
	 */
	public function rollback($silent = false) : bool;
	
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
