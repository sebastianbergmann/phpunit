<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A class with a __toString() method.
 *
 * @since      Class available since Release 3.6.0
 */
class ClassWithToString
{
    public function __toString()
    {
        return 'string representation';
    }
}
