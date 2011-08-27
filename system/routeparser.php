<?php

class RouteParser {
    private $tokens;
    
    private $xml = '';
    private $route = '';

    public function __construct($tokens, $route) {
        $this->tokens = $tokens;
        $this->route = $route;
    }
    
    public function parse() {
        $this->xml .= "<goal>\n";
        $urlSpecification = $this->parseUrlSpecification();
        $this->match('EndToken');
        $this->xml .= "</goal>\n";
        file_put_contents(ROOT_DIRECTORY . DS . SYSTEM_DIRECTORY . DS . sha1($this->route) . '.xml', $this->xml);
    }
    
    private function parseUrlSpecification() {
        $this->xml .= "<url_specification>\n";
        $list = $this->parseList($level + 1);
        $urlSpecification = array();
        $urlSpecification['regexes'] = $list['regexes'];
        array_push($urlSpecification['regexes'], end($list['regexes']) . '\/');
        $urlSpecification['symbols'] = $list['symbols'];
        echo '<pre>' . print_r($urlSpecification, true) . '</pre><br /><br /><br />';
        $this->xml .= "</url_specification>\n";
        return $urlSpecification;
    }
    
    private function parseList() {
        $this->xml .= "<list>\n";
        $list = array();
        $list['regexes'] = array();
        if($this->tokens[0]->isNot('EndToken')) {
            $item = $item = $this->parseItem();
            $list1 = $this->parseList();
            $list['regexes'] = array();
            array_push($list['regexes'], $item['value']);
            foreach($list1['regexes'] as $regex) {
                array_push($list['regexes'], $item['value'] . $regex);
            }
            $list['symbols'] = array();
            if(array_key_exists('symbol', $item)) {
                array_push($list['symbols'], $item['symbol']);
            }
            foreach($list1['symbols'] as $symbol) {
                array_push($list['symbols'], $symbol);
            }
            echo '<pre>' . print_r($list, true) . '</pre><br /><br /><br />';
        } else {
            $list['regexes'] = array();
            $list['symbols'] = array();
        }
        $this->xml .= "</list>\n";
        return $list;
    }
    
    private function parseItem() {
        $this->xml .= "<item>\n";
        $item = array();
        if($this->tokens[0]->is('PlainTextToken')) {
            $token = $this->match('PlainTextToken');
            $item['value'] = $token->getText();
        } else if($this->tokens[0]->is('SlashToken')) {
            $this->match('SlashToken');
            $item['value'] = '\/';
            return $item;
        } else if($this->tokens[0]->is('OpeningParenthesisToken')) {
            $placeholder = $this->parsePlaceholder();
            $item['value'] = $placeholder['value'];
            $item['symbol'] = $placeholder['symbol'];
        } else {
            file_put_contents(ROOT_DIRECTORY . DS . SYSTEM_DIRECTORY . DS . sha1($this->route) . '.xml', $this->xml);
            global $errorHandler;
            $errorHandler->shutdown('Parse error parsing item');
            exit;
        }
        $this->xml .= "</item>\n";
        return $item;
    }
    
    private function parsePlaceholder() {
        $this->xml .= "<placeholder>\n";
        $placeholder = array();
        $this->match('OpeningParenthesisToken');
        $placeholderContents = $this->parsePlaceholderContents();
        $placeholder['value'] = $placeholderContents['value'];
        $placeholder['symbol'] = $placeholderContents['symbol'];
        $this->match('ClosingParenthesisToken');
        $this->xml .= "</placeholder>\n";
        return $placeholder;
    }
    
    private function parsePlaceholderContents() {
        $this->xml .= "<placeholder_contents>\n";
        $placeholderContents = array();
        if($this->tokens[0]->is('AsteriskToken')) {
            $absorbingPlaceholder = $this->parseAbsorbingPlaceholder();
            $placeholderContents['value'] = '([A-Za-z0-9\/]+)';
            $placeholderContents['symbol'] = $absorbingPlaceholder['symbol'];
        } else if($this->tokens[0]->is('PlusSignToken')) {
            $optionalPlaceholder = $this->parseOptionalPlaceholder();
            $placeholderContents['value'] = '([A-Za-z0-9]+)';
            $placeholderContents['symbol'] = $optionalPlaceholder['symbol'];
        } else if($this->tokens[0]->is('PlainTextToken')) {
            $token = $this->match('PlainTextToken');
            $placeholderContents['value'] = '([A-Za-z0-9]+)';
            $placeholderContents['symbol'] = array($token->getText(), 'required');
        } else {
            file_put_contents(ROOT_DIRECTORY . DS . SYSTEM_DIRECTORY . DS . sha1($this->route) . '.xml', $this->xml);
            global $errorHandler;
            $errorHandler->shutdown('Parse error parsing placeholder contents');
            exit;
        }
        $this->xml .= "</placeholder_contents>\n";
        return $placeholderContents;
    }
    
    private function parseAbsorbingPlaceholder() {
        $this->xml .= "<absorbing_placeholder>\n";
        $this->match('AsteriskToken');
        $token = $this->match('PlainTextToken');
        $absorbingPlaceholder = array();
        $absorbingPlaceholder['symbol'] = array($token->getText(), 'optional');
        $this->xml .= "</absorbing_placeholder>\n";
        return $absorbingPlaceholder;
    }
    
    private function parseOptionalPlaceholder() {
        $this->xml .= "<optional_placeholder>\n";
        $this->match('PlusSignToken');
        $token = $this->match('PlainTextToken');
        $optionalPlaceholder = array();
        $optionalPlaceholder['symbol'] = array($token->getText(), 'optional');
        $this->xml .= "</optional_placeholder>\n";
        return $optionalPlaceholder;
    }
    
    public function match($tokenClass) {
        $this->xml .= "<match token_type=\"" . $tokenClass . "\">\n";
        if($this->tokens[0]->is($tokenClass)) {
            $this->xml .= "<matched token=\"" . $this->tokens[0] . "\" />\n";
            $this->xml .= "</match>\n";
            return array_shift($this->tokens);
        } else {
            file_put_contents(ROOT_DIRECTORY . DS . SYSTEM_DIRECTORY . DS . sha1($this->route) . '.xml', $this->xml);
            global $errorHandler;
            $errorHandler->shutdown('Parse error matching ' . $tokenClass . ' token');
            exit;
        }
    }
}