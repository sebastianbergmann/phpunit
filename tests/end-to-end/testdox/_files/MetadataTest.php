<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestDox;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('Text from class-level TestDox metadata')]
final class MetadataTest extends TestCase
{
    #[TestDox('Text from method-level TestDox metadata for successful test')]
    public function testSomethingThatWorks(): void
    {
        $this->assertTrue(true);
    }

    #[TestDox('Text from method-level TestDox metadata for failing test')]
    public function testSomethingThatDoesNotWork(): void
    {
        /* @noinspection PhpUnitAssertTrueWithIncompatibleTypeArgumentInspection */
        $this->assertTrue(false);
    }
}
