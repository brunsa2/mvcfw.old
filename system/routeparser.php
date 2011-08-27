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
        return $urlSpecification;
    }
    
    private function parseUrlSpecification() {
        $this->xml .= "<url_specification>\n";
        $list = $this->parseList(array());
        $urlSpecification = array();
        $urlSpecification['regexes'] = array();
        foreach($list['regexes'] as $regex) {
            array_push($urlSpecification['regexes'], '^' . $regex . '$');
        }
        array_push($urlSpecification['regexes'], '^' . end($list['regexes']) . '\/$');
        $urlSpecification['placeholders'] = $list['placeholders'];
        $this->xml .= "</url_specification>\n";
        return $urlSpecification;
    }
    
    private function parseList($list) {
        $this->xml .= "<list>\n";
        $list['regexes'] = array();
        if($this->tokens[0]->isNot('EndToken')) {
            $item = array();
            $item['hitOptional'] = $list['hitOptional'];
            $item = $this->parseItem($item);
            $nextList = array();
            $nextList['hitOptional'] = $item['hitOptional'] || $list['hitOptional'];
            $nextList = $this->parseList($nextList);
            $list['regexes'] = array();
            array_push($list['regexes'], $item['value']);
            foreach($nextList['regexes'] as $regex) {
                array_push($list['regexes'], $item['value'] . $regex);
            }
            $list['placeholders'] = array();
            if(array_key_exists('placeholder', $item)) {
                array_push($list['placeholders'], $item['placeholder']);
            }
            foreach($nextList['placeholders'] as $placeholder) {
                array_push($list['placeholders'], $placeholder);
            }
        } else {
            $list['regexes'] = array();
            $list['placeholders'] = array();
            $list['hitOptional'] = false;
        }
        $this->xml .= "</list>\n";
        return $list;
    }
    
    private function parseItem($item) {
        $this->xml .= "<item>\n";
        if($this->tokens[0]->is('BeginningToken')) {
            $this->match('BeginningToken');
            $item['value'] = '';
            $item['hitOptional'] = $item['hitOptional'] ? true : false;
        } else if($this->tokens[0]->is('PlainTextToken')) {
            $token = $this->match('PlainTextToken');
            $item['value'] = $token->getText();
            $item['hitOptional'] = $item['hitOptional'] ? true : false;
        } else if($this->tokens[0]->is('SlashToken')) {
            $this->match('SlashToken');
            $item['value'] = '\/';
            $item['hitOptional'] = $item['hitOptional'] ? true : false;
        } else if($this->tokens[0]->is('OpeningParenthesisToken')) {
            $placeholder = array();
            $placeholder['hitOptional'] = $item['hitOptional'];
            $placeholder = $this->parsePlaceholder($placeholder);
            $item['value'] = $placeholder['value'];
            $item['placeholder'] = $placeholder['placeholder'];
            $item['hitOptional'] = $item['hitOptional'] ? true : $placeholder['hitOptional'];
        } else {
            file_put_contents(ROOT_DIRECTORY . DS . SYSTEM_DIRECTORY . DS . sha1($this->route) . '.xml', $this->xml);
            global $errorHandler;
            $errorHandler->shutdown('Parse error parsing item');
            exit;
        }
        $this->xml .= "</item>\n";
        return $item;
    }
    
    private function parsePlaceholder($placeholder) {
        $this->xml .= "<placeholder>\n";
        $this->match('OpeningParenthesisToken');
        $placeholderContents = array();
        $placeholderContents['hitOptional'] = $placeholder['hitOptional'];
        $placeholderContents = $this->parsePlaceholderContents($placeholderContents);
        $placeholder['value'] = $placeholderContents['value'];
        $placeholder['placeholder'] = $placeholderContents['placeholder'];
        $placeholder['hitOptional'] = $placeholderContents['hitOptional'];
        $this->match('ClosingParenthesisToken');
        $this->xml .= "</placeholder>\n";
        return $placeholder;
    }
    
    private function parsePlaceholderContents($placeholderContents) {
        $this->xml .= "<placeholder_contents>\n";
        if($this->tokens[0]->is('AsteriskToken')) {
            $absorbingPlaceholder = $this->parseAbsorbingPlaceholder();
            $placeholderContents['value'] = '([A-Za-z0-9\/]+)';
            $placeholderContents['placeholder'] = $absorbingPlaceholder['placeholder'];
            $placeholderContents['hitOptional'] = true;
        } else if($this->tokens[0]->is('PlusSignToken')) {
            $optionalPlaceholder = $this->parseOptionalPlaceholder();
            $placeholderContents['value'] = '([A-Za-z0-9]+)';
            $placeholderContents['placeholder'] = $optionalPlaceholder['placeholder'];
            $placeholderContents['hitOptional'] = true;
        } else if($this->tokens[0]->is('PlainTextToken')) {
            if($placeholderContents['hitOptional']) {
                global $errorHandler;
                $errorHandler->shutdown('Semantic error: Cannot have required matches after optional ones');
            }
            $token = $this->match('PlainTextToken');
            $placeholderContents['value'] = '([A-Za-z0-9]+)';
            $placeholderContents['placeholder'] = array($token->getText(), 'required');
            $placeholderContents['hitOptional'] = false;
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
        $absorbingPlaceholder['placeholder'] = array($token->getText(), 'optional');
        $this->xml .= "</absorbing_placeholder>\n";
        return $absorbingPlaceholder;
    }
    
    private function parseOptionalPlaceholder() {
        $this->xml .= "<optional_placeholder>\n";
        $this->match('PlusSignToken');
        $token = $this->match('PlainTextToken');
        $optionalPlaceholder = array();
        $optionalPlaceholder['placeholder'] = array($token->getText(), 'optional');
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