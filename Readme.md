DbHandler Handler
=================

PHP wrapper around the MySQLi Database Extensions, providing intuitive access to prepared queries.

---

European Union Public Licence, v. 1.1

Unless required by applicable law or agreed to in writing, software
distributed under the Licence is distributed on an "AS IS" basis,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.

Contact: info@netsilik.nl
Latest version available at: https://gitlab.com/Netsilik/DbHandler


Installation
------------

```
composer require netsilik/db-handler
```

Usage
-----

```php
use Netsilik\DbHandler;

$dbHandler = new DbHandler('localhost', 'user', 'password', 'test');


$result = $dbHandler->query("INSERT INTO tests VALUES (null, %s)", 'foo');
$result = $dbHandler->query("SELECT * FROM tests ORDER BY id DESC LIMIT 3");
var_dump( $result->getInsertedId() );
var_dump( $result->getAffectedRecords() );
var_dump( $result->getFieldCount() );
var_dump( $result->getRecordCount() );
var_dump( $result->fetch() );
$result->dump();

echo '<hr>';

$result = $dbHandler->rawQuery("INSERT INTO tests VALUES (null, 'foo')");
$result = $dbHandler->query("SELECT * FROM tests ORDER BY id DESC LIMIT 3");
var_dump( $result->getInsertedId() );
var_dump( $result->getAffectedRecords() );
var_dump( $result->getFieldCount() );
var_dump( $result->getRecordCount() );
var_dump( $result->fetch() );
$result->dump();
```
