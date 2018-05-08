<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class FatalTest extends TestCase
{
    public function testFatalError(): void
    {
        if (\extension_loaded('xdebug')) {
            \xdebug_disable();
        }

        eval('class FatalTest {}');
    }
}
