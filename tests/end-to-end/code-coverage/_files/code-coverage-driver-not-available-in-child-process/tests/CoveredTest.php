<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\CodeCoverageDriverNotAvailableInChildProcess;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

#[CoversClass(Covered::class)]
final class CoveredTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testSomething(): void
    {
        $this->assertTrue((new Covered)->doSomething());
    }
}
