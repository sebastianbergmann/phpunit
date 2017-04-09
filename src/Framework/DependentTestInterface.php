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

interface DependentTestInterface extends Test
{
    /**
     * Get list of tests name that this test depends from
     *
     * @return string
     */
    public function getDependencies();

    /**
     * @return string
     */
    public function getName();
}
