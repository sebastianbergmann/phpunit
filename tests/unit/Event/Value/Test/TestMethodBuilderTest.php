<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\Metadata\Attribute\ForAllClosureTest;
use PHPUnit\TestFixture\Metadata\Attribute\ForAllTest;

#[CoversClass(TestMethodBuilder::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/value-objects')]
final class TestMethodBuilderTest extends TestCase
{
    public function testTheMetadataOfATestMethodIncludesTheGeneratorsThatAreSpecifiedWithTheNameOfAMethod(): void
    {
        $metadata = TestMethodBuilder::fromTestCase(new ForAllTest('testOne'))->metadata()->isForAll();

        $this->assertCount(1, $metadata);
    }

    #[RequiresPhp('^8.5')]
    public function testTheMetadataOfATestMethodDoesNotIncludeTheGeneratorsThatAreSpecifiedWithAClosure(): void
    {
        $this->assertCount(0, TestMethodBuilder::fromTestCase(new ForAllClosureTest('testOne'))->metadata()->isForAll());
    }
}
