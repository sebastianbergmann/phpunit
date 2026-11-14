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

use function array_reverse;
use PHPUnit\Framework\Attributes\Property;
use PHPUnit\Framework\TestCase;

final class PassingPropertyTest extends TestCase
{
    /**
     * @param list<int> $input
     */
    #[Property(examples: 10)]
    public function testReversingTwiceIsIdentity(array $input): void
    {
        $this->assertSame($input, array_reverse(array_reverse($input)));
    }
}
