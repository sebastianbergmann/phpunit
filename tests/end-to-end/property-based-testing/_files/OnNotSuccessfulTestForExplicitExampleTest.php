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

use const PHP_EOL;
use const STDOUT;
use function fwrite;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Throwable;

final class OnNotSuccessfulTestForExplicitExampleTest extends TestCase
{
    #[Property]
    #[TestWith([-1])]
    public function testValueIsNotNegative(int $value): void
    {
        $this->assertGreaterThanOrEqual(0, $value);
    }

    protected function onNotSuccessfulTest(Throwable $t): never
    {
        fwrite(STDOUT, 'onNotSuccessfulTest() was called: ' . $t->getMessage() . PHP_EOL);

        throw $t;
    }
}
