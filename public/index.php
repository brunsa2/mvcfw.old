<?php

if(!defined(DS)) {
	define(DS, DIRECTORY_SEPARATOR);
}

if(!defined(ROOT_DIRECTORY)) {
	define(ROOT_DIRECTORY, dirname(dirname(__FILE__)));
}

if(!defined(REWRITE_ENGINE)) {
	define(REWRITE_ENGINE, true);
}

define(SYSTEM_DIRECTORY, 'system');
define(CONFIG_DIRECTORY, 'config');
define(BOOTSTRAP, 'bootstrap.php');

require_once(ROOT_DIRECTORY . DS . SYSTEM_DIRECTORY . DS . BOOTSTRAP);

?>