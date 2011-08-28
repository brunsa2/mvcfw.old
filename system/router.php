<?php

class Router {
    private $routeTable;
  
    public function __construct($routeTable) {
        $this->routeTable = $routeTable;
    }
    
    public function init() {
        if(is_array($this->routeTable)) {
            foreach($this->routeTable as $routeKey => $route) {
                $scanner = new RouteScanner();
                $tokens = $scanner->scanRoute($route->url);
                
                $parser = new RouteParser($tokens, $route->url);
                $compiledRoute = $parser->parse();
                
                $placeholders = array();
                $defaults = (array) $route->defaults;
                foreach($compiledRoute['placeholders'] as $placeholder) {
                    foreach($defaults as $name => $default) {
                        if($placeholder[0] == $name) {
                            $placeholder[2] = $default;
                            unset($defaults[$name]);
                            
                            if($route->controller && $route->controller == $name) {
                                $placeholder[3] = 'controller';
                            } else if($route->action && $route->action == $name) {
                                $placeholder[3] = 'action';
                            }
                        }
                        
                    }
                    array_push($placeholders, $placeholder);
                }
                foreach($defaults as $name => $default) {
                    $placeholder = array();
                    $placeholder[0] = $name;
                    $placeholder[1] = 'optional';
                    $placeholder[2] = $default;
                    array_push($placeholders, $placeholder);
                }
                $foundController = false;
                $foundAction = false;
                foreach($placeholders as $placeholder) {
                    if($placeholder[3] && $placeholder[3] == 'controller') {
                        $foundController = true;
                    } else if($placeholder[3] && $placeholder[3] == 'action') {
                        $foundAction = true;
                    }
                }
                $setController = false;
                if(!$foundController) {
                    $adjustedPlaceholders = array();
                    foreach($placeholders as $placeholder) {
                        if(!$setController && !$placeholder[3]) {
                            $placeholder[3] = 'controller';
                            $setController = true;
                        }
                        array_push($adjustedPlaceholders, $placeholder);
                    }
                    $placeholders = $adjustedPlaceholders;
                }
                $setAction = false;
                if(!$foundAction) {
                    $adjustedPlaceholders = array();
                    foreach($placeholders as $placeholder) {
                        if(!$setAction && !$placeholder[3]) {
                            $placeholder[3] = 'action';
                            $setAction = true;
                        }
                        array_push($adjustedPlaceholders, $placeholder);
                    }
                    $placeholders = $adjustedPlaceholders;
                }
                $compiledRoute['placeholders'] = $placeholders;
                
                $this->routeTable[$routeKey] = $compiledRoute;
            }
            
            echo '<pre>' . print_r($this->routeTable, true) . '</pre>';
        } else {
            global $errorHandler;
            $errorHandler->shutdown('Route table is not an array of routes');
        }
      
        return $this;
    }
    
    public function findRoute() {
        
    }
}

?>