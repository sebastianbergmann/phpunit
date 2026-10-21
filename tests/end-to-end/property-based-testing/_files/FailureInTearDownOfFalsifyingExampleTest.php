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

final class FailureInTearDownOfFalsifyingExampleTest extends TestCase
{
    protected function tearDown(): void
    {
        if ($this->status()->isFailure()) {
            $this->fail('failure in tearDown()');
        }
    }

    #[Property]
    public function testFailsWhenTheValueIsTooLarge(int $value): void
    {
        $this->assertLessThan(10, $value);
    }
}
