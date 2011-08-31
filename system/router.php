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
                
                $parser = new RouteParser($tokens);
                $compiledRoute = $parser->parse();
                
                $placeholders = array();
                $defaults = (array) $route->defaults;
                $regexes = (array) $route->regexes;
                foreach($compiledRoute['placeholders'] as $placeholder) {
                    foreach($defaults as $name => $default) {
                        if($placeholder['name'] == $name) {
                            $placeholder['defaultValue'] = $default;
                            if($regexes[$name]) {
                                $placeholder['regex'] = $regexes[$name];
                            }
                            unset($defaults[$name]);
                        }
                    }
                    if($route->controller && $route->controller == $placeholder['name']) {
                        $placeholder['routing'] = 'controller';
                    } else if($route->action && $route->action == $placeholder['name']) {
                        $placeholder['routing'] = 'action';
                    } 
                    array_push($placeholders, $placeholder);
                }
                foreach($defaults as $name => $default) {
                    $placeholder = array();
                    $placeholder['name'] = $name;
                    $placeholder['type'] = 'optional';
                    $placeholder['defaultValue'] = $default;
                    array_push($placeholders, $placeholder);
                }
                $foundController = false;
                $foundAction = false;
                foreach($placeholders as $placeholder) {
                    if($placeholder['routing'] && $placeholder['routing'] == 'controller') {
                        $foundController = true;
                    } else if($placeholder['routing'] && $placeholder['routing'] == 'action') {
                        $foundAction = true;
                    }
                }
                $setController = false;
                if(!$foundController) {
                    $adjustedPlaceholders = array();
                    foreach($placeholders as $placeholder) {
                        if(!$setController && !$placeholder['routing']) {
                            $placeholder['routing'] = 'controller';
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
                        if(!$setAction && !$placeholder['routing']) {
                            $placeholder['routing'] = 'action';
                            $setAction = true;
                        }
                        array_push($adjustedPlaceholders, $placeholder);
                    }
                    $placeholders = $adjustedPlaceholders;
                }
                $compiledRoute['placeholders'] = $placeholders;
                
                $this->routeTable[$routeKey] = $compiledRoute;
            }
        } else {
            global $errorHandler;
            $errorHandler->shutdown('Route table is not an array of routes');
        }
      
        return $this;
    }
    
    public function findRoute($routeUrl) {
        foreach($this->routeTable as $route) {
            $route['regexes'] = array_reverse($route['regexes']);
            foreach($route['regexes'] as $regex) {
                if(preg_match($regex, $routeUrl, $matches)) {
                    if($match = $this->attemptRouteMatch($route['placeholders'], $matches)) {
                        return $match;
                    }
                }
            }
        }
    }
    
    private function attemptRouteMatch($placeholders, $matches) {
        array_shift($matches);
        $routeMatch = array();
        $matchValues = array();
        foreach($matches as $position => $match) {
            if($placeholders[$position]) {
                $placeholders[$position]['value'] = $match;
            }
        }
        foreach($placeholders as $placeholder) {
            if(!$placeholder['value']) {
                $placeholder['value'] = $placeholder['defaultValue'];
            }
            
            if($placeholder['type'] == 'required') {
                if(!$placeholder['value']) {
                    return false;
                }
            }
            
            if($placeholder['regex'] && !preg_match($placeholder['regex'], $placeholder['value'])) {
              return false;
            }
            
            if($placeholder['routing'] == 'controller') {
                $routeMatch['controller'] = $placeholder['value'];
            } else if($placeholder['routing'] == 'action') {
                $routeMatch['action'] = $placeholder['value'];
            }
            
            unset($placeholder['type']);
            unset($placeholder['regex']);
            unset($placeholder['routing']);
            unset($placeholder['match']);
            unset($placeholder['defaultValue']);
            
            array_push($matchValues, $placeholder);
        }
        if($routeMatch['controller'] == '' || $routeMatch['action'] == '') {
            return false;
        }
        $routeMatch['values'] = $matchValues;
        return $routeMatch;
    }
}

?>