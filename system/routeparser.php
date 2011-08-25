<?php

class RouteParser {
    private $tokens;
    
    private $xml = '';
    private $route = '';

    public function RouteParser($tokens, $route) {
        $this->tokens = $tokens;
        $this->route = $route;
    }
    
    public function parse() {
        $this->xml .= "<goal>\n";
        $this->parseUrlSpecification();
        $this->match('EndToken');
        $this->xml .= "</goal>\n";
        file_put_contents(ROOT_DIRECTORY . DS . SYSTEM_DIRECTORY . DS . sha1($this->route) . '.xml', $this->xml);
    }
    
    private function parseUrlSpecification() {
        $this->xml .= "<url_specification>\n";
        $this->parseList($level + 1);
        $this->xml .= "</url_specification>\n";
    }
    
    private function parseList() {
        $this->xml .= "<list>\n";
        if($this->tokens[0]->isNot('EndToken')) {
            $this->parseItem();
            $this->parseList();
        }
        $this->xml .= "</list>\n";
    }
    
    private function parseItem() {
        $this->xml .= "<item>\n";
        if($this->tokens[0]->is('PlainTextToken')) {
            $this->match('PlainTextToken');
        } else if($this->tokens[0]->is('SlashToken')) {
            $this->match('SlashToken');
        } else if($this->tokens[0]->is('OpeningParenthesisToken')) {
            $this->parsePlaceholder();
        } else {
            echo 'Parse error parsing item<br />';
            file_put_contents(ROOT_DIRECTORY . DS . SYSTEM_DIRECTORY . DS . sha1($this->route) . '.xml', $this->xml);
            exit;
        }
        $this->xml .= "</item>\n";
    }
    
    private function parsePlaceholder() {
        $this->xml .= "<placeholder>\n";
        $this->match('OpeningParenthesisToken');
        $this->parsePlaceholderContents();
        $this->match('ClosingParenthesisToken');
        $this->xml .= "</placeholder>\n";
    }
    
    private function parsePlaceholderContents() {
        $this->xml .= "<placeholder_contents>\n";
        if($this->tokens[0]->is('AsteriskToken')) {
            $this->parseAbsorbingPlaceholder();
        } else if($this->tokens[0]->is('PlusSignToken')) {
            $this->parseOptionalPlaceholder();
        } else if($this->tokens[0]->is('PlainTextToken')) {
            $this->match('PlainTextToken');
        } else {
            echo 'Parse error parsing placeholder contents<br />';
            file_put_contents(ROOT_DIRECTORY . DS . SYSTEM_DIRECTORY . DS . sha1($this->route) . '.xml', $this->xml);
            exit;
        }
        $this->xml .= "</placeholder_contents>\n";
    }
    
    private function parseAbsorbingPlaceholder() {
        $this->xml .= "<absorbing_placeholder>\n";
        $this->match('AsteriskToken');
        $this->match('PlainTextToken');
        $this->xml .= "</absorbing_placeholder>\n";
    }
    
    private function parseOptionalPlaceholder() {
        $this->xml .= "<optional_placeholder>\n";
        $this->match('PlusSignToken');
        $this->match('PlainTextToken');
        $this->xml .= "</optional_placeholder>\n";
    }
    
    public function match($tokenClass) {
        $this->xml .= "<match token_type=\"" . $tokenClass . "\">\n";
        if($this->tokens[0]->is($tokenClass)) {
            $this->xml .= "<matched token=\"" . $this->tokens[0] . "\" />\n";
            $this->xml .= "</match>\n";
            array_shift($this->tokens);
        } else {
            echo 'Parse error matching ' . $tokenClass . '<br />';
            file_put_contents(ROOT_DIRECTORY . DS . SYSTEM_DIRECTORY . DS . sha1($this->route) . '.xml', $this->xml);
            exit;
        }
    }
}