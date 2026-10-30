<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Version;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidVersionRequirement::class)]
#[Small]
#[Group('metadata')]
final class InvalidVersionRequirementTest extends TestCase
{
    public function testCanBeRepresentedAsString(): void
    {
        $requirement = new InvalidVersionRequirement('message');

        $this->assertSame('message', $requirement->asString());
    }
}
