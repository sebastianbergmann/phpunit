<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\DataProvider;

use PHPUnit\Framework\Attributes\DataProviderClosure;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ClosureThrowsExceptionTest extends TestCase
{
    #[DataProviderClosure(static function (): array
    {
        throw new RuntimeException('closure failed');
    })]
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
