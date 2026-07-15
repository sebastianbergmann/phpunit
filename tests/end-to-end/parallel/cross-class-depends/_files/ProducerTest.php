<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelCrossClassDepends;

use PHPUnit\Framework\TestCase;

final class ProducerTest extends TestCase
{
    public function testProduces(): string
    {
        $this->assertTrue(true);

        return 'produced value';
    }
}
