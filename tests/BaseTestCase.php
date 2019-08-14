<?php
namespace Tests;

/**
 * @package       netsilik/db-handler
 * @copyright (c) 2011-2016 Netsilik (http://netsilik.nl)
 * @license       EUPL-1.1 (European Union Public Licence, v1.1)
 */

use Netsilik\Testing\BaseTestCase AS NetsilikBaseTestCase;


abstract class BaseTestCase extends NetsilikBaseTestCase
{
	/**
	 * {@inheritDoc}
	 */
	public function __construct($name = null, array $data = [], $dataName = '')
	{
		parent::__construct($name, $data, $dataName);
		
		$this->_convertNoticesToExceptions  = false;
		$this->_convertWarningsToExceptions = false;
		$this->_convertErrorsToExceptions   = true;
	}
}
