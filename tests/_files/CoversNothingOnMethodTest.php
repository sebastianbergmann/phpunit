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

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

final class CoversNothingOnMethodTest extends TestCase
{
    #[CoversNothing]
    public function testSomething(): void
    {
        $o = new CoveredClass;

        $o->publicMethod();

        $this->assertTrue(true);
    }
}
