<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting;

use function str_starts_with;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class ExplicitExamplesOfPropertyThatRejectsItsGeneratedExamplesTest extends TestCase
{
    #[Property(examples: 10)]
    #[TestWith(['explicit example 1'])]
    #[TestWith(['explicit example 2'])]
    public function testAcceptsOnlyItsExplicitExamples(string $value): void
    {
        $this->assume(str_starts_with($value, 'explicit example'));

        $this->assertStringStartsWith('explicit example', $value);
    }
}
