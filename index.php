<?php

define(DS, DIRECTORY_SEPARATOR);
define(ROOT_DIRECTORY, dirname(__FILE__));
define(PUBLIC_DIRECTORY, 'public');
define(INDEX, 'index.php');

define('REWRITE_ENGINE', false);

require_once(ROOT_DIRECTORY . DS . PUBLIC_DIRECTORY . DS . INDEX);

?>