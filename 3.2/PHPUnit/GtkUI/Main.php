<?php
/**
 * PHP-GTK2 Test Runner for PHPUnit
 *
 * Copyright (c) 2007, Tobias Schlitt <toby@php.net>.
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
 *   * Neither the name of Tobias Schlitt nor the names of his
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
 * @author     Tobias Schlitt <toby@php.net>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2007 Tobias Schlitt <toby@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/GtkUI/Controller/FileChooser.php';
require_once 'PHPUnit/GtkUI/Controller/MainWindow.php';
require_once 'PHPUnit/GtkUI/Controller/SuiteTree.php';
require_once 'PHPUnit/GtkUI/Runner/Simple.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * A Runner that uses PHP-GTK2.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Tobias Schlitt <toby@php.net>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2007 Tobias Schlitt <toby@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_GtkUI_Main
{
    private $testRunner;
    private $glade;

    private static $instance;
    
    public static function getInstance()
    {
        if (self::$instance === NULL) {
            self::$instance = new PHPUnit_GtkUI_Main;
            self::$instance->init();
        }

        return self::$instance;
    }

    private function init()
    {

        if (substr(php_sapi_name(), 0, 3) !== "cli") {
            throw new RuntimeException("This program does only work with the PHP CLI SAPI.");
        }
        if (!extension_loaded("php-gtk")) {
            throw new RuntimeException("This program requires ext/php-gtk for running.");
        }

        // Autoload inclusion hack
        // TODO: This should be handled somehow from the GUI
        if (isset($_SERVER["argv"][1]) === true && function_exists("__autoload") === false) {
            require_once $_SERVER["argv"][1];
        }

        $this->glade = new GladeXML(
            dirname(__FILE__) . '/glade/testrunner.glade'
        );

        $this->glade->signal_autoconnect_instance($this);

        PHPUnit_GtkUI_Controller_MainWindow::getInstance()->show();

        $this->testRunner = new PHPUnit_GtkUI_Runner_Simple;
    }

    public function getRunner()
    {
        return $this->testRunner;
    }

    public function loadSuite()
    {
        $chooser = $this->glade->get_widget('wndFileOpen');

        if ($chooser->run() !== Gtk::RESPONSE_OK) {
            $chooser->hide();
            return;
        }

        $chooser->hide();
        $this->testRunner->loadSuite($chooser->get_filename());
        PHPUnit_GtkUI_Controller_MainWindow::getInstance()->updateSuiteTree();
    }

    public function loadMultipleSuites()
    {
        $chooser = PHPUnit_GtkUI_Controller_FileChooser::getInstance();

        if ($chooser->run() !== Gtk::RESPONSE_OK) {
            $chooser->hide();
            return;
        }

        $chooser->hide();
        $this->testRunner->loadSuites($chooser->getFilenames());
        PHPUnit_GtkUI_Controller_MainWindow::getInstance()->updateSuiteTree();
    }

    public static function processEvents()
    {
        while (Gtk::events_pending())
        {
            Gtk::main_iteration();
        }
    }

    public function run()
    {
        Gtk::main();
    }

    public function runTests()
    {
        $this->testRunner->doRun(PHPUnit_GtkUI_Controller_SuiteTree::getInstance()->model);
    }

    public function testProgress($fraction)
    {
        PHPUnit_GtkUI_Controller_MainWindow::getInstance()->indicateProgress($fraction);
        self::processEvents();
    }
    
    public function __call($signal, $arguments)
    {
        switch ($signal) {
            case 'suite_open': {
                $this->loadSuite();
            }
            break;

            case 'suites_open': {
                $this->loadMultipleSuites();
            }
            break;

            case 'run_suites': {
                $this->runTests();
            }
            break;
                    
            case 'show_about': {
                $this->showAbout();
            }
            break;
        }
    }
    
    public function __get($attributeName)
    {
        switch ($attributeName) {
            case 'glade': {
                return $this->glade;
            }
            break;
            
            default: {
                throw new RuntimeException;
            }
        }
    }


    private function showAbout()
    {
        $this->glade->get_widget('wndAbout')->run();
    }
}

PHPUnit_GtkUI_Main::getInstance()->run();
?>
