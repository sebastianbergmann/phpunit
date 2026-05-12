<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6610;

use PHPUnit\Framework\TestCase;

final class Issue6610Test extends TestCase
{
    public function testHelperClassFromTestSuiteBootstrapIsAvailable(): void
    {
        $this->assertSame('loaded', (new Issue6610Helper)->value());
    }
}
