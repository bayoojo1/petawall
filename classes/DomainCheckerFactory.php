<?php

class DomainCheckerFactory {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            require_once __DIR__ . '/WhoApiDomainChecker.php';
            self::$instance = new WhoApiDomainChecker(WHOAPI_API_KEY);
        }
        return self::$instance;
    }
}