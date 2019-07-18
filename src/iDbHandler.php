<?php
namespace Netsilik\DbHandler;

/**
 * @package DbHandler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license EUPL-1.1 (European Union Public Licence, v1.1)
 */

interface iDbHandler {
	
	public function query(string $query, array $params = []);
}
