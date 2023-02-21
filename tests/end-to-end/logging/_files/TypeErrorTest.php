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

use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class TypeErrorTest extends TestCase
{
    private DateTimeImmutable $dateTime;

    protected function setUp(): void
    {
        $this->dateTime = new DateTime;
    }

    public function testMe(): void
    {
        $this->assertTrue(true);
    }
}
