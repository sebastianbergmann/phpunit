<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use PHPUnit\Framework\TestCase;

final class StandardTestSuiteLoaderTest extends TestCase
{
    public function testIssue5139(): void
    {
        $this->expectException(Exception::class);

        $file = (new \ReflectionClass(Exception::class))->getFileName();

        (new StandardTestSuiteLoader())->load($file);
    }
}
