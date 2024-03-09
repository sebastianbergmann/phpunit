<?php

declare(strict_types=1);

namespace PHPUnit\TestFixture\CodeCoverage;

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\PHPUnit\TestFixture\CodeCoverage\FullyTestedClass::class)]
class FullyTestedClassTest extends \PHPUnit\Framework\TestCase
{
    public function testMethod(): void
    {
        $this->assertTrue((new FullyTestedClass)->method());
    }
}
