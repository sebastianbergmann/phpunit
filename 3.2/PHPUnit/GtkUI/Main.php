<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2007, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @author     Tobias Schlitt <toby@php.net>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.1.0
 */

require_once 'PHPUnit/GtkUI/Controller/FileChooser.php';
require_once 'PHPUnit/GtkUI/Controller/MainWindow.php';
require_once 'PHPUnit/GtkUI/Controller/SuiteTree.php';
require_once 'PHPUnit/GtkUI/TestRunner.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * A TestRunner that uses PHP-GTK2.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Tobias Schlitt <toby@php.net>
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.1.0
 */
class PHPUnit_GtkUI_Main
{
    protected $testRunner;
    protected static $instance;
    protected $glade;

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

    public static function getInstance()
    {
        if (self::$instance === NULL) {
            self::$instance = new PHPUnit_GtkUI_Main;
            self::$instance->init();
        }

        return self::$instance;
    }

    protected function init()
    {
        $this->glade = new GladeXML(
          dirname(__FILE__) . '/glade/testrunner.glade'
        );

        $this->glade->signal_autoconnect_instance($this);

        PHPUnit_GtkUI_Controller_MainWindow::getInstance()->show();

        $this->testRunner = new PHPUnit_GtkUI_TestRunner;
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

    public function getGladeXml()
    {
        return $this->glade;
    }

    protected function showAbout()
    {
        $this->glade->get_widget('wndAbout')->run();
    }
}

PHPUnit_GtkUI_Main::getInstance()->run();
?>
