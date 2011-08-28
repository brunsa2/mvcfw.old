<?php

class RouteScanner {
    public function scanRoute($route) {
        $tokens = array();
        
        array_push($tokens, new BeginningToken());
        
        while(self::hasCharacter($route)) {
            $character = self::lookAtTopCharacter($route);
            $route = self::removeTopCharacter($route);
            
            if(self::isOpeningBrace($character)) {
                array_push($tokens, new OpeningBraceToken());
            } else if(self::isClosingBrace($character)) {
                array_push($tokens, new ClosingBraceToken());
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
            } else {
                global $errorHandler;
                $errorHandler->shutdown('Scan error at character ' . $character);
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
        return preg_match('/[^{}\/]/', $character) > 0;
    }
    
    private static function isOpeningBrace($character) {
        return preg_match('/{/', $character) > 0;
    }
    
    private static function isClosingBrace($character) {
        return preg_match('/}/', $character) > 0;
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
        $escapedText = $this->plainText;
        $escapedText = str_replace('+', '\+', $escapedText);
        $escapedText = str_replace('*', '\*', $escapedText);
        $escapedText = str_replace('$', '\$', $escapedText);
        $escapedText = str_replace('.', '\.', $escapedText);
        $escapedText = str_replace('(', '\(', $escapedText);
        $escapedText = str_replace(')', '\)', $escapedText);
        return $escapedText;
    }
    
    public function __toString() {
        return 'T_PLAIN_TEXT (' . $this->plainText . ')';
    }
}

class OpeningBraceToken extends Token {
    public function __toString() {
        return 'T_OPENING_BRACE';
    }
}

class ClosingBraceToken extends Token {
    public function __toString() {
        return 'T_CLOSING_BRACE';
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

class BeginningToken extends Token {
    public function __toString() {
        return 'T_BEGINNING_OF_STREAM';
    }
}

class EndToken extends Token {
    public function __toString() {
        return 'T_END_OF_STREAM';
    }
}

?>