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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoLogNoCc::class)]
final class NoLogNoCcTest extends TestCase
{
    public function testSuccess(): void
    {
        $this->assertTrue((new NoLogNoCc)->getTrue());
    }
}
