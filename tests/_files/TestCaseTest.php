<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TestCaseTest extends TestCase
{
    public function testOne(): void
    {
    }

    #[Test]
    public function two(): void
    {
    }

    public function three(): void
    {
    }

    private function four(): void
    {
    }
}
