<?php

class Router {
    private $routeTable;
  
    public function Router($routeTable) {
        $this->routeTable = $routeTable;
    }
    
    public function init() {
        // TODO: Separate out the router from the route compiler
        if(is_array($this->routeTable)) {
            foreach($this->routeTable as $route) {
                $compiledRoute = new RouteCompiler($route);
            }
        } else {
            // TODO: Error handling
        }
      
        return $this;
    }
    
    public function findRoute() {
        
    }
}

?>