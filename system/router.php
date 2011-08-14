<?php

class Router {
    private $routeTable;
  
    public function Router($routeTable) {
        $this->routeTable = $routeTable;
    }
    
    public function init() {
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