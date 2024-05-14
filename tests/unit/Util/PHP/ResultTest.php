<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\PHP;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Result::class)]
#[Small]
final class ResultTest extends TestCase
{
    public function testHasOutputFromStdout(): void
    {
        $this->assertSame('stdout', $this->fixture()->stdout());
    }

    public function testHasOutputFromStderr(): void
    {
        $this->assertSame('stderr', $this->fixture()->stderr());
    }

    private function fixture(): Result
    {
        return new Result('stdout', 'stderr');
    }
}
