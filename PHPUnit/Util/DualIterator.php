<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PHPUnit
 *
 * Copyright (c) 2002-2006, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Marcus Börger <helly@php.net>
 * @author     Kore Nordmann <mail@kore-nordmann.de>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.0.0
 */

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 *
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Marcus Börger <helly@php.net>
 * @author     Kore Nordmann <mail@kore-nordmann.de>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2006 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_Util_DualIterator implements Iterator
{
    const CURRENT_LHS   = 0x01;
    const CURRENT_RHS   = 0x02;
    const CURRENT_ARRAY = 0x03;
    const CURRENT_0     = 0x00;

    const KEY_LHS   = 0x10;
    const KEY_RHS   = 0x20;
    const KEY_ARRAY = 0x30;
    const KEY_0     = 0x00;

    const DEFAULT_FLAGS = 0x33;

    private $lhs;
    private $rhs;
    private $flags;

    public function __construct(Iterator $lhs, Iterator $rhs, $flags = 0x33)
    {
        $this->lhs   = $lhs;
        $this->rhs   = $rhs;
        $this->flags = $flags;
    }

    public function getLHS()
    {
        return $this->lhs;
    }

    public function getRHS()
    {
        return $this->rhs;
    }

    public function setFlags($flags)
    {
        $this->flags = $flags;
    }

    public function getFlags()
    {
        return $this->flags;
    }

    public function rewind()
    {
        $this->lhs->rewind();
        $this->rhs->rewind();
    }

    public function valid()
    {
        return $this->lhs->valid() && $this->rhs->valid();
    }

    public function current()
    {
        switch($this->flags & 0x0F) {
            default:
            case self::CURRENT_ARRAY: {
              return array($this->lhs->current(), $this->rhs->current());
            }

            case self::CURRENT_LHS: {
              return $this->lhs->current();
            }

            case self::CURRENT_RHS: {
              return $this->rhs->current();
            }

            case self::CURRENT_0: {
              return NULL;
            }
        }
    }

    public function key()
    {
        switch($this->flags & 0xF0) {
            default:
            case self::CURRENT_ARRAY: {
              return array($this->lhs->key(), $this->rhs->key());
            }

            case self::CURRENT_LHS: {
              return $this->lhs->key();
            }

            case self::CURRENT_RHS: {
              return $this->rhs->key();
            }

            case self::CURRENT_0: {
              return NULL;
            }
        }
    }

    public function next()
    {
        $this->lhs->next();
        $this->rhs->next();
    }

    public function areIdentical()
    {
        return $this->valid()
             ? $this->lhs->current() === $this->rhs->current()
            && $this->lhs->key()     === $this->rhs->key()
             : $this->lhs->valid()   ==  $this->rhs->valid();
    }

    public function areEqual($delta)
    {
        if ($this->valid()) {
            if ($this->lhs->key() == $this->rhs->key()) {
                if ($this->lhs->current() == $this->rhs->current()) {
                    return TRUE;
                }

                if (is_numeric($this->lhs->current()) &&
                    is_numeric($this->rhs->current()) &&
                    abs($this->lhs->current() - $this->rhs->current()) <= $delta) {
                    return TRUE;
                }
            }
        } else {
            return $this->lhs->valid() == $this->rhs->valid();
        }
    }

    public static function compareIterators(Iterator $lhs, Iterator $rhs, $identical = FALSE, $delta = .0)
    {
        if ($lhs instanceof RecursiveIterator) {
            if ($rhs instanceof RecursiveIterator) {
                $it = new PHPUnit_Util_RecursiveDualIterator($lhs, $rhs, self::CURRENT_0 | self::KEY_0);
            } else {
                return FALSE;
            }
        } else {
            $it = new PHPUnit_Util_DualIterator($lhs, $rhs, self::CURRENT_0 | self::KEY_0);
        }

        if ($identical) {
            foreach (new RecursiveIteratorIterator($it) as $n) {
                if (!$it->areIdentical()) {
                    return FALSE;
                }
            }
        } else {
            foreach ($it as $n) {
                if (!$it->areEqual($delta)) {
                    return FALSE;
                }
            }
        }

        return $identical ? $it->areIdentical() : $it->areEqual($delta);
    }
}

require_once 'PHPUnit/Util/RecursiveDualIterator.php';

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
