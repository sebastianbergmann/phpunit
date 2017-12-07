<?php

use PHPUnit\TextUI\ResultPrinter;

class CustomPrinter2 extends ResultPrinter
{
    public function __construct(
        $out = null,
        $verbose = false,
        $colors = self::COLOR_DEFAULT,
        $debug = false,
        $numberOfColumns = 80,
        $reverse = false
    ) {
        if (!defined('STDOUT')) {
            define('STDOUT', fopen('php://stdout', 'w'));
        }

        parent::__construct(STDOUT, $verbose, $colors, $debug, $numberOfColumns, $reverse);
    }
}
