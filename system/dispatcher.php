<?php

class Dispatcher {
    public function dispatch($route) {
        if(is_file(ROOT_DIRECTORY . DS . CONTROLLER_DIRECTORY . DS . strtolower($route['controller']) . '_controller.php')) {
            require_once(ROOT_DIRECTORY . DS . CONTROLLER_DIRECTORY . DS . strtolower($route['controller']) . '_controller.php');
            
            $controller = new ReflectionClass($route['controller'] . 'Controller');
            $controllerInstance = $controller->newInstance();
            $action = $controller->getMethod($route['action']);
            $parameters = $action->getParameters();
            $actionArguments = array();
            foreach($parameters as $parameter) {
                $foundParameter = false;
                foreach($route['values'] as $value) {
                    if($value['name'] == $parameter->name) {
                        array_push($actionArguments, $value['value']);
                        $foundParameter = true;
                    }
                }
                if(!$foundParameter) {
                    array_push($actionArguments, null);
                }
            }
            $action->invokeArgs($controllerInstance, $actionArguments);
        } else {
            global $errorHandler;
            $errorHandler->shutdown('Controller file was not found');
        }
    }
}

?>