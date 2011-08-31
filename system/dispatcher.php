<?php

class Dispatcher {
    public function dispatch($route) {
        if(is_file(ROOT_DIRECTORY . DS . CONTROLLER_DIRECTORY . DS . strtolower($route['controller']) . '_controller.php')) {
            require_once(ROOT_DIRECTORY . DS . CONTROLLER_DIRECTORY . DS . strtolower($route['controller']) . '_controller.php');
            
            $controller = new ReflectionClass($route['controller'] . 'Controller');
        } else {
            global $errorHandler;
            $errorHandler->shutdown('Controller was not found');
        }
    }
}

?>