<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelDataProvider;

use function getenv;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

final class ReceivingTest extends TestCase
{
    #[DataProviderExternal(ProvidingTest::class, 'provider')]
    public function testOne(bool $value): void
    {
        $this->assertTrue($value);
        $this->assertNotFalse(getenv('PHPUNIT_WORKER_ID'));
    }
}
