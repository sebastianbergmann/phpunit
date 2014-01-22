<?php
class Singleton
{
    private static $uniqueInstance = null;

    protected function __construct()
    {
    }

    private final function __clone()
    {
    }

    public static function getInstance()
    {
        if (self::$uniqueInstance === null) {
            self::$uniqueInstance = new Singleton;
        }

        return self::$uniqueInstance;
    }
}
