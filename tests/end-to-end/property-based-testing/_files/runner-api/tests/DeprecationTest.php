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

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class DeprecationTest extends TestCase
{
    /**
     * @param int<0, 9> $value
     */
    #[Property]
    public function testExpectsADeprecation(int $value): void
    {
        $this->expectUserDeprecationMessage('deprecated');

        trigger_error('deprecated', E_USER_DEPRECATED);
    }
}
