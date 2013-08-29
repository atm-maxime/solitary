<?php

ini_set('display_errors', true);
ini_set('max_execution_time', '30');

define('ROOT', '/var/www/perso/solitary/');
define('HTTP', 'http://localhost/perso/solitary/');
define('COREHTTP', 'http://localhost/atm/core/');
define('COREROOT', '/var/www/atm/core/');

define('USE_TBS', true);

require(COREROOT.'inc.core.php');

require('class/solitary.class.php');