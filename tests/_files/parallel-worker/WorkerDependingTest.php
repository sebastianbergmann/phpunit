<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorker;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

final class WorkerDependingTest extends TestCase
{
    public static function provider(): array
    {
        return [[true]];
    }

    public function testProducer(): void
    {
        $this->assertTrue(true);
    }

    #[Depends('testProducer')]
    public function testConsumer(): void
    {
        $this->assertTrue(true);
    }

    #[DataProvider('provider')]
    #[Depends('testProducer')]
    public function testDataProvidedConsumer(bool $value): void
    {
        $this->assertTrue($value);
    }
}
