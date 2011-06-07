<?php

class Author
{
    // the order of properties is important for testing the cycle!
    public $books = array();

    private $name = '';

    public function __construct($name)
    {
        $this->name = $name;
    }
}