<?php
namespace Netsilik\DbHandler;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use mysqli;
use stdClass;
use Exception;
use mysqli_stmt;
use InvalidArgumentException;
use Netsilik\DbHandler\DbResult\iDbResult;
use Netsilik\DbHandler\DbResult\DbRawResult;
use Netsilik\DbHandler\DbResult\DbStatementResult;


/**
 * Database Access abstraction class
 */
class DbHandler implements iDbHandler
{
	/**
	 * Connect timeout in seconds
	 */
	const CONNECTION_TIMEOUT = 10;
	
	/**
	 * The name of the default connection charset (4 byte UTF-8)
	 */
	const CONNECTION_CHARSET = 'utf8mb4';
	
	/**
	 * The name of the default connection collation
	 */
	const CONNECTION_COLLATION = 'utf8mb4_unicode_ci';
	
	/**
	 * Retry MySQL queries immediately, for error codes in this list
	 */
	const RETRY_IMMEDIATELY_ERROR_CODES = [
		2006, // 'MySQL server has gone away'
		2013, // 'Lost connection to MySQL server during query'
	];
	
	/**
	 * Retry MySQL queries with delay, for error codes in this list
	 */
	const RETRY_WITH_DELAY_ERROR_CODES = [
		1205, // 'Lock wait timeout exceeded'
		1213, // 'Deadlock found when trying to get lock'
	];
	
	/**
	 * @var mysqli $_connection
	 */
	protected $_connection;
	
	/**
	 * @var bool $_inTransaction
	 */
	protected $_inTransaction = false;
	
	/**
	 * @var string $_password
	 */
	private $_password;
	
	/**
	 * @var string $_userName
	 */
	private $_userName;
	
	/**
	 * @var string $_database
	 */
	private $_database;
	
	/**
	 * @var string $_host
	 */
	private $_host;
	
	/**
	 * @var string $_caCertFile
	 */
	private $_caCertFile;
	
	/**
	 * Constructor
	 *
	 * @param string      $host
	 * @param string      $database
	 * @param string      $userName
	 * @param string      $password
	 * @param string|null $caCertFile
	 */
	public function __construct(string $host, string $userName, string $password, string $database = null, string $caCertFile = null)
	{
		$this->_host       = $host;
		$this->_userName   = $userName;
		$this->_password   = $password;
		$this->_database   = $database;
		$this->_caCertFile = $caCertFile;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function connect() : iDbHandler
	{
		$this->_connection = mysqli_init(); // Create MySQLi instance
		if (!($this->_connection instanceof mysqli)) {
			throw new Exception('Could not initialize MySQLi instance');
		}
		
		if (null !== $this->_caCertFile) {
			// Make sure the CA certificate file is available so that we can setup an encrypted connection
			if (false === ($caCertFile = realpath($this->_caCertFile))) {
				throw new Exception('CA-Certificate file could not be found');
			}
			
			$this->_connection->ssl_set(null, null, $caCertFile, null, null);
		}
		
		// Set connect timeout
		$this->_connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, self::CONNECTION_TIMEOUT);
		
		// Open connection
		if (!$this->_connection->real_connect($this->_host, $this->_userName, $this->_password) || null !== $this->_connection->connect_error) {
			$this->_connection = null;
			throw new Exception('Could not connect to DB-server: ' . $this->_connection->connect_error);
		}
		
		// Make sure the connection character set and collation matches our expectation
		$this->setConnectionCharSet(self::CONNECTION_CHARSET);
		$this->setConnectionCollation(self::CONNECTION_COLLATION);
		
		// Select specified database (if any)
		if (null !== $this->_database) {
			$this->selectDb($this->_database);
		}
		
		return $this;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function isConnected() : bool
	{
		if (!($this->_connection instanceof mysqli)) {
			trigger_error('attempted to ping, but connection not initialized', E_USER_WARNING);
			return false;
		}
		
		return $this->_connection->ping();
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getConnection() : mysqli
	{
		return $this->_connection;
	}
	
	/**
	 * Set the character set for the connection
	 *
	 * @param string $characterSet The name of the character set to use
	 *
	 * @return iDbHandler $this
	 */
	public function setConnectionCharSet($characterSet) : iDbHandler
	{
		$this->_connection->set_charset($characterSet);
		
		return $this;
	}
	
	/**
	 * Set the collation for the connection
	 *
	 * @param string $collation The name of the collation to use
	 *
	 * @return iDbHandler $this
	 *
	 * @throws \Exception
	 */
	public function setConnectionCollation($collation) : iDbHandler
	{
		$this->query('SET collation_connection = %s', [$collation]);
		
		return $this;
	}
	
	/**
	 * Get the information on the server and clients character set and collation settings
	 *
	 * @return \Netsilik\DbHandler\DbResult\iDbResult A AbstractDbResult object holding the result of the executed query
	 * @throws \Exception
	 */
	public function getCharsetAndCollationInfo() : iDbResult
	{
		return $this->query("SHOW VARIABLES WHERE Variable_name LIKE 'character\_set\_%' OR Variable_name LIKE 'collation%'");
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function selectDb(string $dbName) : bool
	{
		return $this->_connection->select_db($dbName);
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function query(string $query, array $params = [], int $failRetryCount = 3) : iDbResult
	{
		list($query, $params) = $this->_preParse(trim($query), $params);
		
		if (strlen($params[0]) <> count($params) - 1) {
			throw new InvalidArgumentException((count($params) - 1) . ' parameters specified, ' . strlen($params[0]) . ' expected');
		}
		
		$this->_ensureConnected();
		
		$statement = $this->_connection->prepare($query);
		if (!($statement instanceof mysqli_stmt) || $statement->errno > 0) {
			throw new Exception('Query preparation failed: ' . $this->_connection->error);
		}
		
		if ((strlen($params[0]) > 0 && false === call_user_func_array([$statement, 'bind_param'], $this->_referenceValues($params))) || $statement->errno > 0) {
			throw new Exception('Parameter binding failed');
		}
		
		
		$startTime = microtime(true);
		
		if (!$this->_executePreparedStatement($statement, $failRetryCount)) {
			throw new Exception('Query failed: ' . $statement->error);
		}
		
		$executionTime = microtime(true) - $startTime;
		
		return new DbStatementResult($statement, $executionTime);
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function rawQuery(string $query) : iDbResult
	{
		$this->_ensureConnected();
		
		$startTime = microtime(true);
		if ( false === ($result = $this->_connection->query($query)) ) {
			trigger_error('query failed: '.$this->_connection->error.' ('.$this->_connection->errno.')', E_USER_ERROR);
		}
		$queryTime = microtime(true) - $startTime;
		
		if ($result === true) {
			$result = new stdClass();
			$result->insert_id = $this->_connection->insert_id;
			$result->affected_rows = $this->_connection->affected_rows;
		}
		
		return new DbRawResult($result, $queryTime);
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function escape(string $value) : string
	{
		$this->_ensureConnected();
		
		return $this->_connection->escape_string($value);
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function startTransaction() : bool
	{
		if ($this->_inTransaction) {
			trigger_error('Implicit commit for previous transaction', E_USER_NOTICE);
		} else {
			$this->_connection->autocommit(false);
		}
		
		$this->_inTransaction = true;
		
		return $this->_connection->begin_transaction();
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function rollback($silent = false) : bool
	{
		if (!$silent && !$this->_inTransaction) {
			trigger_error('No transaction started', E_USER_NOTICE);
			
			return false;
		}
		$this->_inTransaction = false;
		
		$result = $this->_connection->rollback();
		$this->_connection->autocommit(true);
		
		return $result;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function commit() : bool
	{
		if ( ! $this->_inTransaction) {
			trigger_error('No transaction started', E_USER_NOTICE);
			return false;
		}
		$this->_inTransaction = false;
		
		$result = $this->_connection->commit();
		$this->_connection->autocommit(true);
		return $result;
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function close() : void
	{
		if ($this->_connection instanceof mysqli) {
			$this->_connection->close();
		}
		
		$this->_connection = null;
	}
	
	// ---[ Private methods ]---------------------------------------------
	
	/**
	 * Re-establish the connection with the MySQL server if we are currently disconnected
	 *
	 * @return void
	 * @throws \Exception
	 */
	private function _ensureConnected() : void
	{
		if (!$this->isConnected()) {
			trigger_error('Reconnecting to DB', E_USER_NOTICE);
			$this->connect();
		}
	}
	
	/**
	 * Parse the query for parameter placeholders and, if appropriate, match them to the number of elements in the parameter
	 *
	 * @param string $query The query string with indexed or named parameter placeholders
	 * @param array $params The parameters in either an index or associative array
	 *
	 * @return array An indexed array with two elements, the first element is the query, the second element an indexed array with token string and the parameter values
	 * @throws \InvalidArgumentException
	 */
	private function _preParse(string $query, array $params) : array
	{
		$s = $d = false; // quoted string quote type
		$queryLength = strlen($query);
		
		$usedNamedParameters   = false;
		$usesIndexedParameters = false;
		
		$parsedParams = ['']; // first element is the token string
		for ($i = 0; $i < $queryLength; $i++) {
			switch ($query{$i}) {
				case '\\':
					$i++;
					break;
				case '\'':
					if (!$d) { $s = !$s; }
					break;
				case '"':
					if (!$s) { $d = !$d; }
					break;
				case '%':
					if (!$s && !$d && $i + 1 < $queryLength) {
						if (false !== strpos('ifsb', $query{$i + 1})) { // look ahead: can we find a valid parameter type indicator
							if ($i + 2 < $queryLength && $query{$i + 2} === ':') { // look ahead: can we find the named parameter indicator
								if ($usesIndexedParameters) {
									throw new InvalidArgumentException('Mixed indexed and named parameters not supported, please use one or the other');
								}
								$usedNamedParameters = true;
								
								$paramName = '';
								for ($j = $i + 3; false !== stripos('abcdefghijklmnopqrstuvwxyz0123456789_', $query{$j}); $j++) {
									$paramName .= $query{$j};
								}
								
								if (!isset($params[ $paramName ])) {
									throw new InvalidArgumentException('Named parameter ' . $paramName . ' not found');
								}
								if (is_array($params[ $paramName ])) {
									throw new InvalidArgumentException('Array parameter expansion is not supported for named parameters');
								}
								
								$parsedParams[0] .= ($query{$i + 1} === 'f' ? 'd' : $query{$i + 1});
								$parsedParams[]  = $params[ $paramName ];
								
								$query = substr_replace($query, '?', $i, 3 + strlen($paramName));
								$queryLength -= 2 + strlen($paramName);
							} else { // This is a non-named parameter
								if ($usedNamedParameters) {
									throw new InvalidArgumentException('Mixed named and indexed parameters not supported, please use one or the other');
								}
								if (count($params) === 0) {
									throw new InvalidArgumentException('The number of parameters is not equal to the number of placeholders');
								}
								
								$usesIndexedParameters = true;
								
								if (is_array($params[0])) {
									if (1 !== preg_match('/(ALL|ANY|IN|SOME)\s*\(\s*$/i', substr($query, 0, $i))) { // look behind: are we in an IN clause?
										throw new InvalidArgumentException('Array parameter expansion is only supported in the ALL, ANY, IN and SOME operators');
									}
									
									$param = array_shift($params);
									$elementCount = count($param);
									
									array_push($parsedParams, ...$param);
									
									$parsedParams[0] .= str_repeat($query{$i + 1}, $elementCount);
									
									$query = substr_replace($query, implode(',', array_fill(0, $elementCount, '?')), $i, 2);
									$queryLength += $elementCount * 2 - 3;
									
								} else {
									$parsedParams[0] .= ($query{$i + 1} === 'f' ? 'd' : $query{$i + 1});
									$query = substr_replace($query, '?', $i, 2);
									$queryLength--;
									
									$parsedParams[] = array_shift($params);
								}
							}
						}
					}
					break;
				//	case: check for the various comment start chars (not implemented yet)
			}
		}
		
		return [
			$query,
			$parsedParams,
		];
	}
	
	/**
	 * @param \mysqli_stmt $statement
	 * @param int          $failRetryCount
	 *
	 * @return bool True on success, false otherwise
	 * @throws \Exception
	 */
	private function _executePreparedStatement(mysqli_stmt $statement, int $failRetryCount) : bool
	{
		$success = $statement->execute();
		
		if (!$success || $statement->errno > 0) {
			if ($failRetryCount > 0 && $this->_errorNoIsRetryable($statement->errno)) {
				if ($this->_errorNoIsRetryableAfterSleep($statement->errno)) {
					usleep(random_int(100000, 400000)); // sleep 0.1 - 0.4 seconds
				}
				
				return $this->_executePreparedStatement($statement, $failRetryCount - 1); // Note: recursion
			}
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * @param int $errorno
	 *
	 * @return bool
	 */
	private function _errorNoIsRetryable(int $errorno) : bool
	{
		return ($this->_errorNoIsRetryableAfterSleep($errorno) || $this->_errorNoIsRetryableImmediately($errorno));
	}
	
	/**
	 * @param int $errorno
	 *
	 * @return bool
	 */
	private function _errorNoIsRetryableAfterSleep(int $errorno) : bool
	{
		return in_array($errorno, self::RETRY_WITH_DELAY_ERROR_CODES);
	}
	
	/**
	 * @param int $errorno
	 *
	 * @return bool
	 */
	private function _errorNoIsRetryableImmediately(int $errorno) : bool
	{
		return in_array($errorno, self::RETRY_IMMEDIATELY_ERROR_CODES);
	}
	
	/**
	 * PHP expects the parameters passed to mysqli_stmt::bind_param to be references. However, we will do the binding after query execution
	 * So, this functions quickly solves the issues by wrapping the arguments in an associative array.
	 *
	 * @param $array
	 *
	 * @return array An associative array
	 */
	private function _referenceValues(array $array) : array
	{
		$references = [];
		foreach ($array as $key => $value) {
			$references[ $key ] = &$array[ $key ];
		}
		
		return $references;
	}
	
	/**
	 * Close current db connection on object destruction
	 */
	public function __destruct()
	{
		$this->close();
	}
}
