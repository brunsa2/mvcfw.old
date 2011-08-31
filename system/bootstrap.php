<?php

define('ROUTE', $_GET['controller_route_url']);

$errorHandler = new ErrorHandler();

$configuration = new Configuration();
$router = new Router($configuration->getConfiguration('routes'));
$route = $router->init()->findRoute(ROUTE);

function __autoload($className) {
	if(is_file(ROOT_DIRECTORY . DS . SYSTEM_DIRECTORY . DS . strtolower($className) . '.php')) {
		require_once(ROOT_DIRECTORY . DS . SYSTEM_DIRECTORY . DS . strtolower($className) . '.php');
	} else {
		if($className == 'ErrorHandler') {
			echo 'System cannot load error handling facility';
			exit;
		} else {
			global $errorHandler;
			$errorHandler->shutdown('System cannot load ' . $className . ' system class.');
		}
	}
}

?>