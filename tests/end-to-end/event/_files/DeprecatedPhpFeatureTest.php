<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use function strlen;
use PHPUnit\Framework\TestCase;

final class DeprecatedPhpFeatureTest extends TestCase
{
    public function testDeprecatedPhpFeature(): void
    {
        strlen(null);
        @strlen(null);

        $this->assertTrue(true);
    }
}
