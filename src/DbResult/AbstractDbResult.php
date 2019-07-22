<?php
namespace Netsilik\DbHandler\DbResult;

/**
 * @package DbHandler
 * @copyright (c) 2011-2019 Netsilik (http://netsilik.nl)
 * @license EUPL-1.1 (European Union Public Licence, v1.1)
 */

use Netsilik\DbHandler\Interfaces\iDbResult;


/**
 * Result object, returned by the DbHandler whenever a valid query is executed
 */
abstract class AbstractDbResult implements iDbResult {
		
	/**
	 * @var float $_queryTime The query time in mili seconds
	 */
	protected $_queryTime = 0;
	
	/**
	 * @var array $_result The bound record as field-name => value pairs; modified by reference
	 */
	protected $_result = null;
	
	/**
	 * @inheritDoc
	 */
	public function fetchColumn(string $column = null) : array
	{
		$records = $this->fetch();
		$values = [];
		foreach ($records as $record) {
			foreach ($record as $key => $val) {
				if ( null !== $column && $column <> $key) {
					continue;
				}
				$values[] = $val;
				break;
			}
		}
		return $values;
	}
	
	/**
	 * @inheritDoc
	 */
	public function fetchField(string $field = null, int $recordNum = 0)
	{
		$records = $this->fetch();
		if (null === $field) {
			return reset($records[$recordNum]);
		}
		return isset($records[$recordNum][$field]) ? $records[$recordNum][$field] : null;	
	}
	
	/**
	 * @inheritDoc
	 */
	public function fetchRecord(int $recordNum = 0) : array
	{
		$records = $this->fetch();
		return isset($records[$recordNum]) ? $records[$recordNum] : [];
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getQueryTime() : string
	{
		return number_format($this->_queryTime, 4);
	}
	
	/**
	 * Dump query result to std-out as an html table
	 *
	 * @return void
	 */
	public function dump() : void
	{
		echo "<div class=\"debug\">\n";
		if (null === $this->_result) {
			echo "<strong>Query OK, ".$this->getAffectedRecords()." rows affected (".$this->getQueryTime()." sec.)</strong>\n";
		} elseif ( 0 === ($recordCount = $this->getRecordCount()) ) {
			echo "<strong>empty set (".$this->getQueryTime()." sec.)</strong>\n";
		} else {
			$records = $this->fetch();
			
			echo "<table cellspacing=\"0\" cellpadding=\"3\" border=\"1\">\n";
			for ($i = 0; $i < $recordCount; $i++) {
			
				if ($i == 0) {
					$fieldNames = array_keys($records[0]);
					echo "\t<tr>\n";
					foreach ($fieldNames as $fieldName) {
						echo "\t\t<th>".$fieldName."</th>\n";
					}
					echo "\t</tr>\n";
				}
				
				echo "\t<tr>\n";
				foreach ($records[$i] as $field) {
					echo "\t\t<td" . (is_numeric($field) ? ' align="right"': '') . '>';
					echo (is_null($field)) ? '<em>NULL</em>' : htmlspecialchars($field, ENT_NOQUOTES);
					echo "</td>\n";
				}
				echo "\t</tr>\n";
				
			}
			echo "</table>\n";
			echo "<strong>$recordCount row in set (".$this->getQueryTime()." sec.)</strong>\n";
		}
		echo "</div>\n";
	}
}
