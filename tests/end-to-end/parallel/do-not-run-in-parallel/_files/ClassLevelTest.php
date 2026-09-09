<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\DoNotRunInParallel;

use PHPUnit\Framework\Attributes\DoNotRunInParallel;
use PHPUnit\Framework\TestCase;

#[DoNotRunInParallel]
final class ClassLevelTest extends TestCase
{
    public function testClassLevel(): void
    {
        $this->assertTrue(true);
    }
}
