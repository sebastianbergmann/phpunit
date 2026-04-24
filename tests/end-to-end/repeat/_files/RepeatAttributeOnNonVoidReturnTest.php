<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Repeat;

use PHPUnit\Framework\Attributes\Repeat;
use PHPUnit\Framework\TestCase;

final class RepeatAttributeOnNonVoidReturnTest extends TestCase
{
    #[Repeat(3)]
    public function testWithReturnValue(): int
    {
        $this->assertTrue(true);

        return 1;
    }
}
