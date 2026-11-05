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

use const PHP_INT_MAX;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class LargestNumberOfExamplesTest extends TestCase
{
    #[Property(examples: PHP_INT_MAX)]
    public function testIsFalsifiedByTheFirstExample(int $value): void
    {
        $this->fail('falsified');
    }
}
