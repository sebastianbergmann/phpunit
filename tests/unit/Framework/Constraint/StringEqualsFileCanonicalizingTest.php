<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\ExpectationFailedException;

use function file_put_contents;

/**
 * @small
 */
final class StringEqualsFileCanonicalizingTest extends TestCase
{
    public function testFailsIfFileNotExists(): void
    {
        $filename = '/tmp/non-existent-file';

        try {
            $this->assertStringEqualsFileCanonicalizing($filename, 'expected');
        } catch (ExpectationFailedException $exception) {
            $expectedMessage = 'Failed asserting that file "/tmp/non-existent-file" exists.';

            $this->assertEquals($expectedMessage, $exception->getMessage());
        }
    }

    public function testFailsIfValuesDiffer()
    {
        $expected = 'Expected file contents';

        $filename = tempnam(sys_get_temp_dir(), 'phpunit');

        file_put_contents($filename, $expected);

        $this->assertStringEqualsFileCanonicalizing($filename, $expected);
    }
}
