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
     * @var string
     */
    private $pageHeader = <<<EOT
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title>Test Documentation</title>
        <style>
            body {
                text-rendering: optimizeLegibility;
                font-variant-ligatures: common-ligatures;
                font-kerning: normal;
                margin-left: 1vw;
            }

            body > ul {
                max-width: 60vw;
            }

            body > ul > li {
                font-family: Source Serif Pro, PT Sans, Trebuchet MS, Helvetica, Arial;
                font-weight: 400;
                font-size: 1vw;
                line-height: 1.5vw;
            }

            h2 {
                font-family: Tahoma, Helvetica, Arial;
                font-size: 1.5vw;
            }

            ul {
                margin-bottom: 1em;
            }
        </style>
    </head>
    <body>
EOT;

    /**
     * @var string
     */
    private $classHeader = <<<EOT

        <h2 id="%s">%s</h2>
        <ul>

EOT;

    /**
     * @var string
     */
    private $classFooter = <<<EOT
        </ul>
EOT;

    /**
     * @var string
     */
    private $pageFooter = <<<EOT

    </body>
</html>
EOT;

    /**
     * Handler for 'start run' event.
     */
    protected function startRun()
    {
        $this->write($this->pageHeader);
    }

    /**
     * Handler for 'start class' event.
     *
     * @param string $name
     */
    protected function startClass($name)
    {
        $this->write(
            sprintf(
                $this->classHeader,
                $name,
                $this->currentTestClassPrettified
            )
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

        $this->write('            <li>' . $strikeOpen . $name . $strikeClose . "</li>\n");
    }

    /**
     * Handler for 'end class' event.
     *
     * @param string $name
     */
    protected function endClass($name)
    {
        $this->write($this->classFooter);
    }

    /**
     * Handler for 'end run' event.
     */
    protected function endRun()
    {
        $this->write($this->pageFooter);
    }
}
