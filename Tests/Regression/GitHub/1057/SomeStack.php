<?php

class SomeStack
{
    private $items = array();

    public static $ctr = 0;

    public function __construct()
    {
        static::$ctr++;
    }

    public function addItem($item)
    {
        $this->items[] = $item;
    }

    public function getItems()
    {
        return $this->items;
    }
}

Class CloneStack extends SomeStack {
    public static $ctr = 0;
}

Class RerunStack extends SomeStack {
    public static $ctr = 0;
}