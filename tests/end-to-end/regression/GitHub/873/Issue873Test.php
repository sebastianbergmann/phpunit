<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (\extension_loaded('xdebug')) {
    \xdebug_disable();
}

    throw new Exception(
        'PHPUnit suppresses exceptions thrown outside of test case function'
    );
