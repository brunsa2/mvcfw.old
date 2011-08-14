<?php

class RouteParser {
    private $tokens;
    private $regex;
    private $text;

    public function RouteParser($tokens) {
        $this->tokens = $tokens;
    }
    
    public function parse() {
        echo '<pre>';
        $parsingUrlSpecification = $this->parseUrlSpecification(0);
        if($parsingUrlSpecification) {
            echo $this->spaces(0) . 'Success parsing<br />';
            $this->parsedUrlSpecificationAction();
        } else {
            echo $this->spaces(0) . 'Parse error<br />';
        }
        echo '</pre>';
        
        echo 'Final regex: ' . $this->regex;
    }
    
    public function parseUrlSpecification($level) {
        echo $this->spaces($level) . 'Parsing URL specification<br />';
        $parsingList = $this->parseList($level + 1);
        echo '<br />';
        $parsingEnd = $this->matchEnd($leve1 + 1);
        if($parsingList && $parsingEnd) {
            echo $this->spaces($level) . 'Success parsing URL specification<br />';
            return true;
        } else {
            echo $this->spaces($level) . 'Parse error parsing URL specification<br />';
            return false;
        }
    }
    
    public function parseList($level) {
        echo $this->spaces($level) . 'Parsing item list<br />';
        if($parsingItem = $this->parseItem($level + 1)) {
            echo '<br />';
            $parsingList = $this->parseList($level + 1);
            
            if($parsingItem && $parsingList) {
                echo $this->spaces($level) . 'Success parsing list item (with additional list item)<br />';
                return true;
            } else {
                echo $this->spaces($level) . 'Parse error parsing list item<br />';
                return false;
            }
        } else {
            echo $this->spaces($level) . 'Success parsing list item (without additional list item)<br />';
            return true;
        }
    }
    
    public function parseItem($level) {
        echo $this->spaces($level) . 'Parsing item<br />';
        if($this->canMatchPlainText($level + 1)) {
            echo $this->spaces($level + 1) . 'Trying to parse item as plain text<br />';
            $parsingPlainText = $this->matchPlainText($level + 2);
            
            if($parsingPlainText) {
                $this->parsedItemAction();
                echo $this->spaces($level + 1) . 'Success parsing plain text<br />';
                return true;
            } else {
                echo $this->spaces($level + 1) . 'Parse error parsing plain text<br />';
                return false;
            }
        } else {
            echo $this->spaces($level + 1) . 'Trying to parse item as placeholder<br />';
            $parsingOpeningParenthesis = $this->matchOpeningParenthesis($level + 2);
            echo '<br />';
            $parsingPlainText = $this->matchPlainText($level + 2);
            echo '<br />';
            $parsingClosingParenthesis = $this->matchClosingParenthesis($level + 2);
            
            if($parsingOpeningParenthesis && $parsingPlainText && $parsingClosingParenthesis) {
                $this->parsedPlaceholderAction();
                $this->parsedItemAction();
                echo $this->spaces($level) . 'Success parsing placeholder<br />';
                return true;
            } else {
                echo $this->spaces($level) . 'Parse error parsing placeholder (failure here might indicate end of list)<br />';
                return false;
            }
        }
    }
    
    public function canMatchPlainText($level) {
        echo $this->spaces($level) . 'Checking for plain text<br />';
        return ($this->tokens[0] instanceof PlainTextToken);
    }
    
    public function matchPlainText($level) {
        echo $this->spaces($level) . 'Matching plain text<br />';
        if($this->tokens[0] instanceof PlainTextToken) {
            $this->matchedPlainTextAction($this->tokens[0]);
            echo $this->spaces($level) . 'Matched ' . $this->tokens[0] . '<br />';
            echo $this->spaces($level) . 'Success matching plain text<br />';
            array_shift($this->tokens);
            return true;
        } else {
            echo $this->spaces($level) . 'Parse error matching plain text<br />';
            return false;
        }
    }
    
    public function matchOpeningParenthesis($level) {
        echo $this->spaces($level) . 'Matching opening parenthesis<br />';
        if($this->tokens[0] instanceof OpeningParenthesisToken) {
            echo $this->spaces($level) . 'Matched ' . $this->tokens[0] . '<br />';
            echo $this->spaces($level) . 'Success matching opening parenthesis<br />';
            array_shift($this->tokens);
            return true;
        } else {
            echo $this->spaces($level) . 'Parse error matching opening parenthesis<br />';
            return false;
        }
    }
    
    public function matchClosingParenthesis($level) {
        echo $this->spaces($level) . 'Matching closing parenthesis<br />';
        if($this->tokens[0] instanceof ClosingParenthesisToken) {
            echo $this->spaces($level) . 'Matched ' . $this->tokens[0] . '<br />';
            echo $this->spaces($level) . 'Success matching closing parenthesis<br />';
            array_shift($this->tokens);
            return true;
        } else {
            echo $this->spaces($level) . 'Parse error matching closing parenthesis<br />';
            return false;
        }
    }
    
    private function matchEnd($level) {
        echo $this->spaces($level) . 'Matching end<br />';
        if($this->tokens[0] instanceof EndToken) {
            echo $this->spaces($level) . 'Success matching end<br />';
            array_shift($this->tokens);
            return true;
        } else {
            echo $this->spaces($level) . 'Parse error matching end<br />';
            return false;
        }
    }
    
    private function spaces($level) {
        $spaces = '';
        for($levelIndex = 0; $levelIndex < $level; $levelIndex++) {
            $spaces = $spaces . '    ';
        }
        return $spaces;
    }
    
    private function parsedUrlSpecificationAction() {
        echo "PARSED URL SPECIFICATION ACTION<br />";
        $this->regex = $this->regex . '/?';
        $this->regex = str_replace('/', '\/', $this->regex);
    }
    
    private function parsedItemAction() {
        echo 'PARSED ITEM ACTION<br />';
        $this->regex = $this->regex . $this->text;
    }
    
    private function matchedPlainTextAction($token) {
        echo "MATCHED PLAIN TEXT ACTION (" . $token . ')<br />';
        $this->text = $token->getText();
    }
    
    private function parsedPlaceholderAction() {
        echo 'MATCHED PLACEHOLDER TEXT ACTION<br />';
        $this->text = '([A-Za-z0-9]+)';
    }
}