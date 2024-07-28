<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

final class Issue5340Test extends TestCase
{
    public function testOne(): void
    {
        print 'output printed from passing test' . \PHP_EOL;

        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        print \PHP_EOL . 'output printed from failing test' . \PHP_EOL;

        $this->assertTrue(false);
    }
}
