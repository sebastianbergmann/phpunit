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

class AssertionExampleTest extends TestCase
{
    public function testOne(): void
    {
        $e = new AssertionExample;

        $e->doSomething();
    }
}
