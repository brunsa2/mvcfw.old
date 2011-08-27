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