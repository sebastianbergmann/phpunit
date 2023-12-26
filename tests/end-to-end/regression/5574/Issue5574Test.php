<?php declare(strict_types=1);

// REFURL: https://github.com/sebastianbergmann/phpunit/issues/5574#issuecomment-1827845849

namespace PHPUnit\TestFixture\Issue5574;

use PHPUnit\Framework\TestCase;

final class Issue5574Test extends TestCase
{
    public function testOne(): void
    {
        $innerException = new \Error('Inner Exception');
        $outerException = new \Exception('My exception', 0, $innerException);

        throw $outerException;
    }
}
