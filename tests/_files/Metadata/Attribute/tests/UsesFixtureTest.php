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

use PHPUnit\Framework\Attributes\UsesFixture;
use PHPUnit\Framework\TestCase;

#[UsesFixture('fixtures')]
final class UsesFixtureTest extends TestCase
{
    #[UsesFixture('fixtures/one.csv')]
    public function testOne(): void
    {
    }
}
