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
use Netsilik\DbHandler\DbResult\DbResult;
use Netsilik\DbHandler\DbResult\DbRawResult;
use Netsilik\DbHandler\DbResult\DbStatementResult;


/**
 * Database Access abstraction class
 */
class DbHandler implements iDbHandler
{
	
	/**
	 * The name of the default connection charset (4 byte UTF-8)
	 */
	const CONNECTION_CHARSET = 'utf8mb4';
	
	/**
	 * The name of the default connection collation
	 */
	const CONNECTION_COLLATION = 'utf8mb4_unicode_ci';
	
	/**
	 * @var mysqli $_connection
	 */
	protected $_connection;
	
	/**
	 * @var bool $_inTransaction
	 */
	protected $_inTransaction;
	
	/**
	 * Constructor
	 *
	 * @param string $dbHost Either a host name or the IP address for the MySQL database server
	 * @param string $dbUser The MySQL user name
	 * @param string $dbPass The MySQL password for this user
	 * @param string|null $dbName The optional name of the database to select
	 *
	 * @throws \Exception
	 */
	public function __construct ($dbHost, $dbUser, $dbPass, $dbName = null)
	{
		$this->_connection = new mysqli($dbHost, $dbUser, $dbPass);
		$this->_inTransaction = false;
	 
		if ( null !== ($errorMsg = $this->_connection->connect_error) ) {
			$this->_connection = null;
			throw new Exception('Could not connect to DB-server: '.$errorMsg);
		}
		
		// Make sure the connection character set and collation matches our expectation
		$this->setConnectionCharSet(self::CONNECTION_CHARSET);
		$this->setConnectionCollation(self::CONNECTION_COLLATION);
		
		// Select specified database (if any)
		if ( $dbName !== null) {
			$this->selectDb( $dbName );
		}
	}
	
	/**
	 * Commit a transaction
	 *
	 * @return bool true on success, false on failure
	 */
	public function commit()
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
	 * Execute a prepared query
	 *
	 * @param string $query The query with zero or more parameter marker characters at the appropriate positions.
	 *                      Parameter markers are defined as a literal % followed by either
	 *                      i : corresponding variable will be interpreted as an integer
	 *                      f : corresponding variable will be interpreted as a float
	 *                      s : corresponding variable will be interpreted as a string
	 *                      b : corresponding variable will be interpreted as a blob and should be sent in packets (but this is not yet supported
	 *                      by MySQL)
	 * @param array $params An optional array, with values matching the parameter markers in $query
	 *
	 * @return \Netsilik\DbHandler\DbResult\DbStatementResult object holding the result of the executed query
	 * @throws \Exception
	 * @throws \InvalidArgumentException
	 */
	public function query(string $query, array $params = []) : DbStatementResult
	{
		list($query, $params) = $this->_preParse(trim($query), $params);
		
		if (strlen($params[0]) <> count($params) - 1) {
			throw new InvalidArgumentException((count($params) - 1) . ' parameters specified, ' . strlen($params[0]) . ' expected');
		}
		
		$statement = $this->_connection->prepare($query);
		if (!($statement instanceof mysqli_stmt) || $statement->errno > 0) {
			throw new Exception('Query preparation failed: ' . $this->_connection->error);
		}
		
		$startTime = microtime(true);
		if ((strlen($params[0]) > 0 && !call_user_func_array([$statement, 'bind_param'], $this->_referenceValues($params))) || $statement->errno > 0) {
			throw new Exception('Parameter binding failed');
		}
		
		$statement->execute();
		$queryTime = microtime(true) - $startTime;
		
		if ($statement->errno > 0) {
			throw new Exception('Query failed: ' . $statement->error);
		}
		
		return new DbStatementResult($statement, $queryTime);
	}
	
	/**
	 * Get the information on the server and clients character set and collation settings
	 * 
	 * @return DbResult A DbResult object holding the result of the executed query
	 * @throws \Exception
	 */
	public function getCharsetAndCollationInfo()
	{
		return $this->query("SHOW VARIABLES WHERE Variable_name LIKE 'character\_set\_%' OR Variable_name LIKE 'collation%'");
	}
	
	/**
	 * Fetch connection resource for this db connection
	 *
	 * @return mysqli mysqli connection
	 */
	public function getConnection()
	{
		return $this->_connection;
	}
	
	/**
	 * Parse the query for parameter placeholders and, if appropriate, match them to the number of elemnts in the parameter
	 *
	 * @param string $query The query string with indexed or named parameter placeholders
	 * @param array $params The parameters in either an index or associative array
	 *
	 * @return array An indexed array with two elements, the first element is the query, the second element an indexed array with token string and the parameter values
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
									$elmentCount = count($param);
									
									array_push($parsedParams, ...$param);
									
									$parsedParams[0] .= str_repeat($query{$i + 1}, $elmentCount);
									
									$query = substr_replace($query, implode(',', array_fill(0, $elmentCount, '?')), $i, 2);
									$queryLength += $elmentCount * 2 - 3;
									
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
	 * Execute a query, as is. Please pay attention to escaping any user provides values
	 *
	 * @param string $query The query to execute
	 * @param bool $multiple Indicate if the $query string contains multiple queries that should be executed
	 *
	 * @return \Netsilik\DbHandler\DbResult\DbRawResult|array A DbRawResult for a single query, an indexed array of DbRawResults if the $multiple parameter was true
	 */
	public function rawQuery(string $query, bool $multiple = false)
	{
		if ($multiple) {
			
			$startTime = microtime(true);
			if ( ! $this->_connection->multi_query($query)) {
				trigger_error('query failed: '.$this->_connection->error.' ('.$this->_connection->errno.')', E_USER_ERROR);
			}
			$queryTime = microtime(true) - $startTime;
			
			$n = 0;
			$records = array();
			do {
				if ($this->_connection->errno > 0) {
					trigger_error('query '.$n.' failed: '.$this->_connection->error.' ('.$this->_connection->errno.')', E_USER_ERROR);
				}
				
				if ( false === ($result = $this->_connection->store_result()) ) {
					$result = new stdClass();
					$result->insert_id = $this->_connection->insert_id;
					$result->affected_rows = $this->_connection->affected_rows;
				}
				$records[$n] = new DbRawResult($result, $queryTime);
				
				$n++;
			} while ( $this->_connection->more_results() && $this->_connection->next_result() );
			
			return $records;
		}
		
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
	 * Escapes special characters in a string for use in an SQL statement, taking into account the current charset of the connection.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function escape(string $value) : string
	{
		return $this->_connection->escape_string($value);
	}
	
	
	/**
	 * PHP expects the parametrs passed to mysqli_stmt::bind_param to be references. However, we will do the binding after query execution
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
	 * Rollback a transaction
	 *
	 * @param bool $silent If false, a E_USER_NOTICE is emitted when no transaction has been started
	 *
	 * @return bool true on success, false on failure
	 */
	public function rollback($silent = false)
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
	 * Select a database to use on current connection
	 *
	 * @param string $dbName name of the database to select
	 *
	 * @return true on success, false otherwise
	 */
	public function selectDb($dbName)
	{
		return $this->_connection->select_db($dbName);
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
	 * Start a transaction ('advanced' features such as WITH CONSISTENT SNAPSHOT are not supported)
	 *
	 * @return bool true on success, false on failure
	 */
	public function startTransaction()
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
	 * Close current db connection on object destruction
	 */
	public function __destruct()
	{
		if ($this->_connection !== null) {
			$this->_connection->close();
			$this->_connection = null;
		}
	}
}
