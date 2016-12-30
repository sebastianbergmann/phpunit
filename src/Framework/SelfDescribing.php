<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework;

/**
 * Interface for classes that can return a description of itself.
 *
 * @since Interface available since Release 3.0.0
 */
interface SelfDescribing
{
    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString();
}
