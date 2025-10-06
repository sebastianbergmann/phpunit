<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ConfigurationFileBasedTestSelection\Groups;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[DoesNotPerformAssertions]
final class ExampleTest extends TestCase
{
    #[Group('one')]
    public function testOne(): void
    {
    }

    #[Group('two')]
    public function testTwo(): void
    {
    }
}
