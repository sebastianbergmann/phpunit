<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Metadata\Attribute;

use LogicException;
use PHPUnit\Framework\Attributes\Retry;
use PHPUnit\Framework\TestCase;

final class RetryTest extends TestCase
{
    #[Retry(1)]
    #[Retry(2, 1)]
    #[Retry(3, 0, LogicException::class)]
    public function testOne(): void
    {
    }
}
