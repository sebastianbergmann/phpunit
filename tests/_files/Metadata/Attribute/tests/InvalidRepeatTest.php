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

use PHPUnit\Framework\Attributes\Repeat;
use PHPUnit\Framework\TestCase;

final class InvalidRepeatTest extends TestCase
{
    #[Repeat(0)]
    public function testWithInvalidNumberOfRepetitions(): void
    {
    }

    #[Repeat(2, 0)]
    public function testWithInvalidFailureThreshold(): void
    {
    }
}
