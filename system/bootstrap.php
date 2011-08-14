<?php

$configuration = new Configuration();
$router = new Router($configuration->getConfiguration('routes'));
$router->init()->findRoute();

function __autoload($className) {
	require_once(ROOT_DIRECTORY . DS . SYSTEM_DIRECTORY . DS . strtolower($className) . '.php');
}

?>