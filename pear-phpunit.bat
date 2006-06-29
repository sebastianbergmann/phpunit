@echo off
REM
REM +------------------------------------------------------------------------+
REM | PEAR :: PHPUnit                                                        |
REM +------------------------------------------------------------------------+
REM | Copyright (c) 2002-2004 Sebastian Bergmann <sb@sebastian-bergmann.de>. |
REM +------------------------------------------------------------------------+
REM | This source file is subject to version 3.00 of the PHP License,        |
REM | that is available at http://www.php.net/license/3_0.txt.               |
REM | If you did not receive a copy of the PHP license and are unable to     |
REM | obtain it through the world-wide-web, please send a note to            |
REM | license@php.net so we can mail you a copy immediately.                 |
REM +------------------------------------------------------------------------+
REM
REM $Id: pear-phpunit.bat,v 1.2.4.1 2005/02/25 05:57:59 sebastian Exp $
REM

"@php_bin@" "@php_dir@/PHPUnit2/TextUI/TestRunner.php" %*
