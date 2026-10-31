<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBased\Examples;

use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExampleWithFailingTearDownTest extends TestCase
{
    protected function tearDown(): void
    {
        throw new RuntimeException('tearDown');
    }

    public function testRejectsTheExample(int $number): void
    {
        $this->assume(false);
    }
}
