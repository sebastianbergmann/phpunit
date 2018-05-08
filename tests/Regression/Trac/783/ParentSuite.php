<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestSuite;

require_once 'ChildSuite.php';

class ParentSuite
{
    public static function suite()
    {
        $suite = new TestSuite('Parent');
        $suite->addTest(ChildSuite::suite());

        return $suite;
    }
}
