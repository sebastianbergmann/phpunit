<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Issue3107;

use PHPUnit\Framework\TestCase;

class Issue3107Test extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        does_not_exist();
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
