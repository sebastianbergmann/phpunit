<?php
class Singleton
{
    private static $uniqueInstance = NULL;

    protected function __construct()
    {
    }

    private final function __clone()
    {
    }

    public static function getInstance()
    {
        if (self::$uniqueInstance === NULL) {
            self::$uniqueInstance = new Singleton;
        }

        return self::$uniqueInstance;
    }
}
