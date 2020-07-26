<?php declare(strict_types = 1);
namespace PHPUnit\TextUI\XmlConfiguration;

use DOMDocument;

interface Migration
{
    public function migrate(DOMDocument $document): void;
}
