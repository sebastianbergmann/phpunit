<?php
declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

class Issue3364Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        throw new \RuntimeException('Something\'s not quite right!');
    }

    public function testSomething(): void
    {
        $this->fail('This cannot work!');
    }
}
