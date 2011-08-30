<?php

class RouteParser {
    private $tokens;

    public function __construct($tokens) {
        $this->tokens = $tokens;
    }
    
    public function parse() {
        $urlSpecification = $this->parseUrlSpecification();
        $this->match('EndToken');
        return $urlSpecification;
    }
    
    private function parseUrlSpecification() {
        $list = $this->parseList(array());
        $urlSpecification = array();
        $urlSpecification['regexes'] = array();
        foreach($list['regexes'] as $regex) {
            array_push($urlSpecification['regexes'], '/^' . $regex . '$/');
        }
        $urlSpecification['placeholders'] = $list['placeholders'];
        return $urlSpecification;
    }
    
    private function parseList($list) {
        $list['regexes'] = array();
        if($this->tokens[0]->isNot('EndToken')) {
            $item = array();
            $item['hitOptional'] = $list['hitOptional'];
            $item = $this->parseItem($item);
            $nextList = array();
            $nextList['hitOptional'] = $list['hitOptional'] || $item['hitOptional'];
            $nextList = $this->parseList($nextList);
            $list['regexes'] = array();
            foreach($nextList['regexes'] as $regex) {
                array_push($list['regexes'], $item['value'] . $regex);
            }
            array_push($list['regexes'], $item['value']);
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
        }
        return $list;
    }
    
    private function parseItem($item) {
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
        } else if($this->tokens[0]->is('OpeningBraceToken')) {
            $placeholder = array();
            $placeholder['hitOptional'] = $item['hitOptional'];
            $placeholder = $this->parsePlaceholder($placeholder);
            $item['value'] = $placeholder['value'];
            $item['placeholder'] = $placeholder['placeholder'];
            $item['hitOptional'] = $item['hitOptional'] ? true : $placeholder['hitOptional'];
        } else {
            global $errorHandler;
            $errorHandler->shutdown('Parse error parsing item');
            exit;
        }
        return $item;
    }
    
    private function parsePlaceholder($placeholder) {
        $this->match('OpeningBraceToken');
        $placeholderContents = array();
        $placeholderContents['hitOptional'] = $placeholder['hitOptional'];
        $placeholderContents = $this->parsePlaceholderContents($placeholderContents);
        $placeholder['value'] = $placeholderContents['value'];
        $placeholder['placeholder'] = $placeholderContents['placeholder'];
        $placeholder['hitOptional'] = $placeholderContents['hitOptional'];
        $this->match('ClosingBraceToken');
        return $placeholder;
    }
    
    private function parsePlaceholderContents($placeholderContents) {
        if($this->tokens[0]->is('AsteriskToken')) {
            $absorbingPlaceholder = $this->parseAbsorbingPlaceholder();
            $placeholderContents['value'] = '(.+)';
            $placeholderContents['placeholder'] = $absorbingPlaceholder['placeholder'];
            $placeholderContents['hitOptional'] = true;
        } else if($this->tokens[0]->is('PlusSignToken')) {
            $optionalPlaceholder = $this->parseOptionalPlaceholder();
            $placeholderContents['value'] = '([^\/]+)';
            $placeholderContents['placeholder'] = $optionalPlaceholder['placeholder'];
            $placeholderContents['hitOptional'] = true;
        } else if($this->tokens[0]->is('PlainTextToken')) {
            if($placeholderContents['hitOptional']) {
                global $errorHandler;
                $errorHandler->shutdown('Semantic error: Cannot have required matches after optional ones');
            }
            $token = $this->match('PlainTextToken');
            $placeholderContents['value'] = '([^\/]+)';
            $placeholderContents['placeholder'] = array();
            $placeholderContents['placeholder']['name'] = $token->getText();
            $placeholderContents['placeholder']['type'] = 'required';
            $placeholderContents['hitOptional'] = false;
        } else {
            global $errorHandler;
            $errorHandler->shutdown('Parse error parsing placeholder contents');
            exit;
        }
        return $placeholderContents;
    }
    
    private function parseAbsorbingPlaceholder() {
        $this->match('AsteriskToken');
        $token = $this->match('PlainTextToken');
        $absorbingPlaceholder = array();
        $absorbingPlaceholder['placeholder'] = array();
        $absorbingPlaceholder['placeholder']['name'] = $token->getText();
        $absorbingPlaceholder['placeholder']['type'] = 'optional';
        return $absorbingPlaceholder;
    }
    
    private function parseOptionalPlaceholder() {
        $this->match('PlusSignToken');
        $token = $this->match('PlainTextToken');
        $optionalPlaceholder = array();
        $optionalPlaceholder['placeholder'] = array();
        $optionalPlaceholder['placeholder']['name'] = $token->getText();
        $optionalPlaceholder['placeholder']['type'] = 'optional';
        return $optionalPlaceholder;
    }
    
    public function match($tokenClass) {
        if($this->tokens[0]->is($tokenClass)) {
            return array_shift($this->tokens);
        } else {
            global $errorHandler;
            $errorHandler->shutdown('Parse error matching ' . $tokenClass . ' token');
            exit;
        }
    }
}