<?php

class RouteCompiler {
    public function RouteCompiler($route) {
        echo '<pre>' . print_r($route, true) . '</pre><br />';
        
        $tokens = $this->scanRoute($route->url);
        
        foreach($tokens as $token) {
            echo $token . '<br />';
        }
        
        echo '<br /><br />';
        
        $parser = new RouteParser($tokens);
        $parser->parse();
    }
    
    private function scanRoute($route) {
        $tokens = array();
        
        while(self::hasCharacter($route)) {
            $character = self::lookAtTopCharacter($route);
            $route = self::removeTopCharacter($route);
            
            if(self::isOpeningParenthesis($character)) {
                array_push($tokens, new OpeningParenthesisToken());
            } else if(self::isClosingParenthesis($character)) {
                array_push($tokens, new ClosingParenthesisToken());
            } else if(self::isPlusSign($character)) {
                array_push($tokens, new PlusSignToken());
            } else if(self::isAsterisk($character)) {
                array_push($tokens, new AsteriskToken());
            } else if(self::isSlash($character)) {
                array_push($tokens, new SlashToken());
            } else if(self::isPlainTextCharacter($character)) {
                $plainText = $character;
                $continueScanningForPlainText = true;
                
                while($continueScanningForPlainText) {
                    $character = self::lookAtTopCharacter($route);
                    if(self::isPlainTextCharacter($character)) {
                        $route = self::removeTopCharacter($route);
                    
                        $plainText = $plainText . $character;
                    } else {
                        $continueScanningForPlainText = false;
                    }
                }
                
                array_push($tokens, new PlainTextToken($plainText));
            }
        }
        
        array_push($tokens, new EndToken());
        
        return $tokens;
    }
    
    private static function hasCharacter($stream) {
        return strlen($stream) > 0;
    }
    
    private static function lookAtTopCharacter($stream) {
        return substr($stream, 0, 1);
    }
    
    private static function removeTopCharacter($stream) {
        return substr($stream, 1);
    }
    
    private static function isPlainTextCharacter($character) {
        return preg_match('/[A-Za-z0-9]/', $character) > 0;
    }
    
    private static function isOpeningParenthesis($character) {
        return preg_match('/\(/', $character) > 0;
    }
    
    private static function isClosingParenthesis($character) {
        return preg_match('/\)/', $character) > 0;
    }
    
    private static function isSlash($character) {
        return preg_match('/\//', $character) > 0;
    }
    
    private static function isPlusSign($character) {
        return preg_match('/\+/', $character) > 0;
    }
    
    private static function isAsterisk($character) {
        return preg_match('/\*/', $character) > 0;
    }
}

abstract class Token {
    public function is($tokenClass) {
        return $this instanceof $tokenClass;
    }
    
    public function isNot($tokenClass) {
        return !($this instanceof $tokenClass);
    }
}

class PlainTextToken extends Token {
    private $plainText;
    
    public function PlainTextToken($plainText) {
        $this->plainText = $plainText;
    }
    
    public function getText() {
        return $this->plainText;
    }
    
    public function __toString() {
        return 'T_PLAIN_TEXT (' . $this->plainText . ')';
    }
}

class OpeningParenthesisToken extends Token {
    public function __toString() {
        return 'T_OPENING_PARENTHESIS';
    }
}

class ClosingParenthesisToken extends Token {
    public function __toString() {
        return 'T_CLOSING_PARENTHESIS';
    }
}

class PlusSignToken extends Token {
    public function __toString() {
        return 'T_PLUS_SIGN';
    }
}

class AsteriskToken extends Token {
    public function __toString() {
        return 'T_ASTERISK';
    }
}

class SlashToken extends Token {
    public function __toString() {
        return 'T_SLASH';
    }
}

class EndToken extends Token {
    public function __toString() {
        return 'T_END_OF_STREAM';
    }
}

?>