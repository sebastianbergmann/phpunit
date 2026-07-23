<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\PhpConfiguration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhpConfigurationCheckResult::class)]
#[Small]
#[Group('test-runner')]
final class PhpConfigurationCheckResultTest extends TestCase
{
    public function testHasName(): void
    {
        $this->assertSame('display_errors', $this->okResult()->name());
    }

    public function testHasValueForConfiguration(): void
    {
        $this->assertSame('On', $this->okResult()->valueForConfiguration());
    }

    public function testHasActualValue(): void
    {
        $this->assertSame('1', $this->okResult()->actualValue());
    }

    public function testMayBeOk(): void
    {
        $this->assertTrue($this->okResult()->isOk());
    }

    public function testMayNotBeOk(): void
    {
        $this->assertFalse($this->notOkResult()->isOk());
    }

    private function okResult(): PhpConfigurationCheckResult
    {
        return new PhpConfigurationCheckResult(
            'display_errors',
            'On',
            '1',
            true,
        );
    }

    private function notOkResult(): PhpConfigurationCheckResult
    {
        return new PhpConfigurationCheckResult(
            'display_errors',
            'On',
            '0',
            false,
        );
    }
}
