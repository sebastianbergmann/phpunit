<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExplicitExample::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class ExplicitExampleTest extends TestCase
{
    public function testHasNameAndArguments(): void
    {
        $example = new ExplicitExample('one cent', ['amount' => 1]);

        $this->assertSame('one cent', $example->name());
        $this->assertSame(['amount' => 1], $example->arguments());
    }

    public function testIsLabelledWithItsNameInQuotes(): void
    {
        $this->assertSame('"one cent"', new ExplicitExample('one cent', [])->label());
    }

    public function testIsLabelledWithItsNumberWhenNoNameIsSpecifiedForIt(): void
    {
        $this->assertSame('#1', new ExplicitExample(1, [])->label());
    }
}
