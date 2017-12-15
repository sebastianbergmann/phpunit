<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\TestDox;

class TestableCliTestDoxPrinter extends CliTestDoxPrinter
{
    private $buffer;

    public function write($text): void
    {
        $this->buffer .= $text;
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }
}
