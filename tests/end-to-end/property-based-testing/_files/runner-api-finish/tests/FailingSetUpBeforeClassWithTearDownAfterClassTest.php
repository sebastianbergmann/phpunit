<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi;

use const PHP_EOL;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class FailingSetUpBeforeClassWithTearDownAfterClassTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        print 'FailingSetUpBeforeClassWithTearDownAfterClassTest::setUpBeforeClass() was called' . PHP_EOL;

        throw new RuntimeException('cannot prepare the test class');
    }

    public static function tearDownAfterClass(): void
    {
        print 'FailingSetUpBeforeClassWithTearDownAfterClassTest::tearDownAfterClass() was called' . PHP_EOL;
    }

    /**
     * @param int<0, 9> $value
     */
    #[Property]
    public function testIsNeverRun(int $value): void
    {
        $this->assertGreaterThanOrEqual(0, $value);
    }
}
