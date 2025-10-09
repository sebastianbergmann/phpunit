<?php

use PHPUnit\Framework\TestCase;
use Greg0ire\PhpunitReproducer\Child6382;

class Issue6382Test extends TestCase
{
    public function testExample(): void
    {
        require_once __DIR__.'/Ancestor.php';
        require_once __DIR__.'/Child.php';

        new Child6382();
    }
}
