<?php

class Dispatcher {
    public function dispatch($route) {
        if(is_file(ROOT_DIRECTORY . DS . CONTROLLER_DIRECTORY . DS . strtolower($route['controller']) . '_controller.php')) {
            require_once(ROOT_DIRECTORY . DS . CONTROLLER_DIRECTORY . DS . strtolower($route['controller']) . '_controller.php');
            
            $controller = new ReflectionClass($route['controller'] . 'Controller');
            $controllerInstance = $controller->newInstance();
            $action = $controller->getMethod($route['action']);
            echo '<h1>Action</h1><pre>' . print_r($action, true). '</pre>';
            $parameters = $action->getParameters();
            echo '<h1>Parameters</h1><pre>' . print_r($parameters, true) . '</pre>';
            $action->invoke($controllerInstance, 'id');
        } else {
            global $errorHandler;
            $errorHandler->shutdown('Controller file was not found');
        }
    }
}

?>