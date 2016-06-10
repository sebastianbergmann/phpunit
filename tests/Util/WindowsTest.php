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
 * @author     Jessica Mauerhan <jessicamauerhan@gmail.com>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 *
 * @link       http://www.phpunit.de/
 * @covers     PHPUnit_Util_PHP
 */
class PHPUnit_Util_PHP_WindowsTest extends PHPUnit_Framework_TestCase
{
    public function testGetCommandShouldReturnCommandCompletelySurroundedByQuotes()
    {
        /**
         * On windows, if the command is: "C:\Program Files (x86)\PHP\php.exe"
         * And this string is passed into proc_open, which runProcess does,
         * Windows will complain:
         *   'C:\Program' is not recognized as an internal or external command,
         *   operable program or batch file.
         *
         * Using an extra set of double quotes around the entire command fixes this.
         * Source: https://bugs.php.net/bug.php?id=49139
         **/
        $windows = new PHPUnit_Util_PHP_Windows();

        $expectedCommandFormat = '""%s""';
        $actualCommand = $windows->getCommand([]);

        $this->assertStringMatchesFormat($expectedCommandFormat, $actualCommand);
    }
}
