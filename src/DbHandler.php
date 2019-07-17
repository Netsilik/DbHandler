<?php
namespace Netsilik\DbHandler;

/**
 * @package DbHandler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license EUPL-1.1 (European Union Public Licence, v1.1)
 *
 *
 * Example of usage:
 * 
 *   // Object creation
 *   $dbPointer = new DbHandler('localhost', 'user', 'secret', 'testDatabase');
 *
 *   // Executing queries
 *   $resultSet = $dbPointer->query('SELECT * FROM test WHERE id = %i, 1);
 *   $records = $resultSet->fetch();
 *
 *   var_dump( $records );
 */

use mysqli;
use stdClass;
use Exception;
use InvalidArgumentException;
use Netsilik\DbHandler\DbResult\DbResult;
use Netsilik\DbHandler\DbResult\DbRawResult;
use Netsilik\DbHandler\DbResult\DbStatementResult;

/**
 * Database Access abstraction object
 */
class DbHandler implements iDbHandler {
	
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
	 * DbHandler constructor.
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
	 * @param string $query the query with zero or more parameter marker characters at the appropriate positions.
	 * Parameter markers are defined as a literal % followed by either
	 * i : corresponding variable will be interpreted as an integer
	 * d : corresponding variable will be interpreted as a double
	 * s : corresponding variable will be interpreted as a string
	 * b : corresponding variable will be interpreted as a blob and should be sent in packets (but this has not yet been implemented)
	 * @param mixed $params An optional set of variables, matching the parameter markers in $query. if an array is given, each element
	 * of the array will be interpreted as being of the type specified by the placeholder
	 *
	 * @return \Netsilik\DbHandler\DbResult\DbStatementResult object holding the result of the executed query
	 * @throws \Exception
	 * @throws \InvalidArgumentException
	 */
	public function query(string $query, array $params = []) : DbStatementResult
	{
		d([
			'query' => $query,
			'params' => $params,
		]);
		list($query, $params) = $this->_parse(trim($query), $params);
		d([
			'query' => $query,
			'params' => $params,
		]);
		
		if (strlen($params[0]) <> count($params) - 1) {
			throw new InvalidArgumentException((count($params) - 1).' parameters specified, '.strlen($params[0]).' expected');
		}
		
		$statement = $this->_connection->prepare($query);
		if ( ! $statement || $statement->errno > 0) {
			throw new Exception('Query preparation failed: '.$this->_connection->error);
		}
		
		$startTime = microtime(true);
		if ( (strlen($params[0]) > 0 && ! call_user_func_array(array($statement, 'bind_param'), $this->_referenceValues($params)) ) || $statement->errno > 0) {
			throw new Exception('Parameter binding failed');
		}
		$statement->execute();
		$queryTime = microtime(true) - $startTime;
		
		if ($statement->errno > 0) {
			throw new Exception('Query failed: '.$statement->error);
		}
		
		return new DbStatementResult( $statement, $queryTime );
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
	 * @return mysqli mysqli connection
	 */
	public function getConnectionPtr()
	{
		return $this->_connection;
	}
	
	/**
	 * Parse the query for parameter placeholders and, if appropriate, match them to the number of elemnts in the parameter
	 * @note both the original variables $query and $params passed to this method will be modified by reference!
	 *
	 * @param string &$query a reference to the query string
	 * @param array &$params a reference to the array with 
	 
	 *
	 * @return array all tokens found by the parser
	 * @throws \Exception
	 * @throws \InvalidArgumentException
	 */
	private function _parse($query, $params)
	{
		$s = $d = false; // quoted string quote type
		$n = 0; // parameter index
		$queryLength = strlen($query);
		
		$usesIndexedParameters = false;
		$usedNamedParameters = false;
		
		$parsedParams = [];
		$tokenString = '';
		for ($i = 0; $i < $queryLength; $i++) {
			switch( $query{ $i } ) {
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
					if (!$s && !$d && $i+1 < $queryLength) {
						if (false !== strpos('ifsb', $query{$i+1})) {

							if ($i+2 < $queryLength && $query{$i+2} === ':') { // found named parameter indicator
								if ($usesIndexedParameters) {
									throw new InvalidArgumentException('Mixed indexed and named parameters not supported, please use one or the other'); // TODO: is InvalidArgumentException the correct exception type?
								}
								$usedNamedParameters = true;
								
								$paramName = '';
								for ($j = $i + 3; false !== stripos('abcdefghijklmnopqrstuvwxyz0123456789_', $query{$j}); $j++) {
									$paramName .= $query{$j};
								}
								
								if (!isset($params[ $paramName ])) {
									throw new InvalidArgumentException('Named parameter '.$paramName.' not found'); // TODO: is InvalidArgumentException the correct exception type?
								}
								
								$tokenString .= ($query{$i+1} === 'f' ? 'd' : $query{$i+1});
								$query = substr_replace($query, '?', $i, 3 + strlen($paramName));
								$queryLength -= 2 + strlen($paramName);
								$n++;
								
								$parsedParams[] = $params[ $paramName ];
								
							} else {
								if ($usedNamedParameters) {
									throw new InvalidArgumentException('Mixed named and indexed parameters not supported, please use one or the other');
								}
								if (count($params) === 0) {
									throw new InvalidArgumentException('The number of parameters is not equal to the number of placeholders'); // TODO: is InvalidArgumentException the correct exception type?
								}
								
								$usesIndexedParameters = true;
								
							//	if (is_array($params[$n])) {
							//
							//		TODO: re-enable the expand array functionality
							//
							//		$elmentCount = count($params[$n]);
							//		array_splice($params, $n, 1, $params[$n]);
							//		$tokenString .= str_repeat($query{$i+1}, $elmentCount);
							//		$query = substr_replace($query, implode(',', array_fill(0, $elmentCount, '?')), $i, 2);
							//		$queryLength += $elmentCount * 2 - 3;
							//		$n += $elmentCount;
							//	} else {
									$tokenString .= ($query{$i+1} === 'f' ? 'd' : $query{$i+1});
									$query = substr_replace($query, '?', $i, 2);
									$queryLength--;
									$n++;
									
									$parsedParams[] = array_shift($params);
									
							//	}
							
							}
							
						}
					}
					break;
				// case: check for the various comment start chars (not implemented yet)
			}
		}
		
		
		array_unshift($parsedParams, $tokenString);
		
		return [
			$query,
			$parsedParams,
		];
	}
	
	/**
	 * @param string $query
	 * @param bool $multiple
	 *
	 * @return \Netsilik\DbHandler\DbResult\DbRawResult | array
	 */
	public function rawQuery($query, $multiple = false)
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
	 * PHP >= 5.3 expects the parametrs passed to mysqli_stmt::bind_param to be references. However, we will do the binding after query execution
	 * So, this functions quickly solves the issues by wrapping the arguments in an associative array.
	 *
	 * @param $array
	 *
	 * @return array An associative array for PHP >= 5.3, unchanged array otherwise
	 */
	private function _referenceValues($array)
	{
		if (strnatcmp(phpversion(),'5.3') >= 0) { // References are required for PHP 5.3+ (and noone knows why)
			$references = array();
			foreach($array as $key => $value) {
				$references[$key] = &$array[$key];
			}
			return $references;
		}
		return $array; 
	}
	
	/**
	 * Rollback a transaction
	 * @param bool $silent If false, a E_USER_NOTICE is emitted when no transaction is started
	 * @return bool true on success, false on failure
	 */
	public function rollback($silent = false)
	{
		if ( ! $silent && ! $this->_inTransaction) {
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
	 * @param string $dbName name of the database to select
	 * @return true on success, false otherwise
	 */
	public function selectDb($dbName)
	{
		return $this->_connection->select_db( $dbName );
	}
	
	/**
	 * Set the character set for the connection
	 * @param string $characterSet The name of the character set to use
	 */
	public function setConnectionCharSet($characterSet)
	{
		$this->_connection->set_charset($characterSet);
	}
	
	/**
	 * Set the collation for the connection
	 * @param string $collation The name of the collation to use
	 *
	 * @throws \Exception
	 */
	public function setConnectionCollation($collation)
	{
		$this->query('SET collation_connection = %s', [$collation]);
	}
	
	/**
	 * Start a transaction
	 * @return bool true on success, false on failure
	 * @note 'advanced' features such as WITH CONSISTENT SNAPSHOT are not supported
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
