<?php

class RouteCompiler {
    public function __construct($route) {
        echo '<pre>' . print_r($route, true) . '</pre><br />';
        
        $scanner = new RouteScanner();
        
        $tokens = $scanner->scanRoute($route->url);
        
        foreach($tokens as $token) {
            echo $token . '<br />';
        }
        
        echo '<br /><br />';
        
        $parser = new RouteParser($tokens, $route->url);
        $parser->parse();
    }
}
    
    

?>