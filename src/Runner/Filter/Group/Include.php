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
 * @since Class available since Release 4.0.0
 */
class PHPUnit_Runner_Filter_Group_Include extends PHPUnit_Runner_Filter_GroupFilterIterator
{
    protected function doAccept($hash)
    {
        return in_array($hash, $this->groupTests);
    }
}
