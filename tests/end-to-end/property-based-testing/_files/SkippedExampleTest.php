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

use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class SkippedExampleTest extends TestCase
{
    /**
     * @param int<0, 10> $value
     */
    #[Property(examples: 10)]
    public function testSkipsTheExample(int $value): void
    {
        $this->markTestSkipped('skipped');
    }
}
