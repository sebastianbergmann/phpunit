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

/**
 * Prints TestDox documentation in text format to files.
 * For the CLI testdox printer please refer to \PHPUnit\TextUI\TextDoxPrinter.
 */
class TextResultPrinter extends ResultPrinter
{
    /**
     * Handler for 'start class' event.
     */
    protected function startClass(string $name): void
    {
        $this->write($this->currentTestClassPrettified . "\n");
    }

    /**
     * Handler for 'on test' event.
     *
     * @param mixed $name
     */
    protected function onTest($name, bool $success = true): void
    {
        if ($success) {
            $this->write(' [x] ');
        } else {
            $this->write(' [ ] ');
        }

        $this->write($name . "\n");
    }

    /**
     * Handler for 'end class' event.
     */
    protected function endClass(string $name): void
    {
        $this->write("\n");
    }
}
