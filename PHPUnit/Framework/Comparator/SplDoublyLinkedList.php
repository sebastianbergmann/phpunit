<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2001-2013, Sebastian Bergmann <sebastian@phpunit.de>.
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
 * @subpackage Framework
 * @author     Sam Boyer <tech@samboyer.org>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.7.29
 */

/**
 * Compares instances of SplDoublyLinkedList and children (SplQueue, SplStack) for equality.
 *
 * @package    PHPUnit
 * @subpackage Framework_Comparator
 * @author     Sam Boyer <tech@samboyer.org>
 * @copyright  2001-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.7.29
 */
class PHPUnit_Framework_Comparator_SplDoublyLinkedList extends PHPUnit_Framework_Comparator_Array
{
    /**
     * Returns whether the comparator can compare two values.
     *
     * @param  mixed $expected The first value to compare
     * @param  mixed $actual The second value to compare
     * @return boolean
     */
    public function accepts($expected, $actual)
    {
        return $expected instanceof SplDoublyLinkedList && $actual instanceof SplDoublyLinkedList;
    }

    /**
     * Asserts that two values are equal.
     *
     * @param  mixed $expected The first value to compare
     * @param  mixed $actual The second value to compare
     * @param  float $delta The allowed numerical distance between two values to
     *                      consider them equal
     * @param  bool  $canonicalize If set to TRUE, arrays are sorted before
     *                             comparison
     * @param  bool  $ignoreCase If set to TRUE, upper- and lowercasing is
     *                           ignored when comparing string values
     * @throws PHPUnit_Framework_ComparisonFailure Thrown when the comparison
     *                           fails. Contains information about the
     *                           specific errors that lead to the failure.
     */
    public function assertEquals($expected, $actual, $delta = 0, $canonicalize = FALSE, $ignoreCase = FALSE)
    {
        // Because PHP implements queues and stacks as specializations of a
        // doubly linked list differentiated only by an iteration mode flag,
        // it is truer to compare the flag than the particular (sub)class in
        // use. Use a mask to focus on only the first two bits, as the SplQueue
        // and SplStack seem to have the third bit set for undocumented reasons.
        $mask = SplDoublyLinkedList::IT_MODE_LIFO | SplDoublyLinkedList::IT_MODE_DELETE;
        if (($actual->getIteratorMode() & $mask) !== ($expected->getIteratorMode() & $mask)) {
            throw new PHPUnit_Framework_ComparisonFailure(
              $expected,
              $actual,
              PHPUnit_Util_Type::export($expected),
              PHPUnit_Util_Type::export($actual),
              FALSE,
              'Failed asserting that both structures are iterating in the same mode.'
            );
        }

        if ($actual->count() !== $expected->count()) {
            throw new PHPUnit_Framework_ComparisonFailure(
              $expected,
              $actual,
              PHPUnit_Util_Type::export($expected),
              PHPUnit_Util_Type::export($actual),
              FALSE,
              'Failed asserting that both structures are equal; they have different length.'
            );
        }

        // If the list is set to delete items when iterating, flip that off
        // while inspecting it.
        $delete = FALSE;
        if ($actual->getIteratorMode() & SplDoublyLinkedList::IT_MODE_DELETE) {
            $delete = TRUE;
            $actual->setIteratorMode($actual->getIteratorMode() & ~SplDoublyLinkedList::IT_MODE_DELETE);
            $expected->setIteratorMode($expected->getIteratorMode() & ~SplDoublyLinkedList::IT_MODE_DELETE);
        }

        $expected->rewind();
        foreach ($actual as $item) {
            $expected->next();
            if ($expected->current() != $item || !$expected->valid()) {
                throw new PHPUnit_Framework_ComparisonFailure(
                  $expected,
                  $actual,
                  PHPUnit_Util_Type::export($expected),
                  PHPUnit_Util_Type::export($actual),
                  FALSE,
                  'Failed asserting that two objects are equal.'
                );
            }
        }

        if ($delete) {
            $actual->setIteratorMode($actual->getIteratorMode() | SplDoublyLinkedList::IT_MODE_DELETE);
            $expected->setIteratorMode($expected->getIteratorMode() | SplDoublyLinkedList::IT_MODE_DELETE);
        }
    }

    protected function toArray(SplDoublyLinkedList $list)
    {
    }
}
