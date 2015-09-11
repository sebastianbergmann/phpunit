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
 * Prints TestDox documentation in HTML format.
 *
 * @since Class available since Release 2.1.0
 */
class PHPUnit_Util_TestDox_ResultPrinter_HTML extends PHPUnit_Util_TestDox_ResultPrinter
{
    /**
     * @var bool
     */
    protected $printsHTML = true;

    /**
     * Handler for 'start run' event.
     */
    protected function startRun()
    {
        $this->write('<html><body>');
    }

    /**
     * Handler for 'start class' event.
     *
     * @param string $name
     */
    protected function startClass($name)
    {
        $this->write(
            '<h2 id="' . $name . '">' . $this->currentTestClassPrettified .
            '</h2><ul>'
        );
    }

    /**
     * Handler for 'on test' event.
     *
     * @param string $name
     * @param bool   $success
     */
    protected function onTest($name, $success = true)
    {
        if (!$success) {
            $strikeOpen  = '<span style="text-decoration:line-through;">';
            $strikeClose = '</span>';
        } else {
            $strikeOpen  = '';
            $strikeClose = '';
        }

        $this->write('<li>' . $strikeOpen . $name . $strikeClose . '</li>');
    }

    /**
     * Handler for 'end class' event.
     *
     * @param string $name
     */
    protected function endClass($name)
    {
        $this->write('</ul>');
    }

    /**
     * Handler for 'end run' event.
     */
    protected function endRun()
    {
        $this->write('</body></html>');
    }
}
