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

final class Issue4037BTest extends TestCase
{
    public function testB(): void
    {
        $this->assertTrue(true);
    }

    public static function ok(): bool
    {
        return true;
    }
}
