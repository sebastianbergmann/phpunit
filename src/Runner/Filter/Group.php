<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit
 * @subpackage Runner
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 4.0.0
 */

/**
 * @package    PHPUnit
 * @subpackage Runner
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 4.0.0
 */
abstract class PHPUnit_Runner_Filter_GroupFilterIterator extends RecursiveFilterIterator
{
    /**
     * @var array
     */
    protected $groupTests = array();

    /**
     * @param RecursiveIterator           $iterator
     * @param array                       $groups
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function __construct(RecursiveIterator $iterator, array $groups, PHPUnit_Framework_TestSuite $suite)
    {
        parent::__construct($iterator);

        foreach ($suite->getGroupDetails() as $group => $tests) {
            if (in_array($group, $groups)) {
                $testHashes = array_map(
                    function ($test) { return spl_object_hash($test);
                    },
                    $tests
                );

                $this->groupTests = array_merge($this->groupTests, $testHashes);
            }
        }
    }

    /**
     * @return boolean
     */
    public function accept()
    {
        $test = $this->getInnerIterator()->current();

        if ($test instanceof PHPUnit_Framework_TestSuite) {
            return true;
        }

        return $this->doAccept(spl_object_hash($test));
    }

    abstract protected function doAccept($hash);
}
