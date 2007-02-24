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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
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

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/GtkUI/Controller/MainWindow.php';
require_once 'PHPUnit/GtkUI/Controller/StatusTree.php';
require_once 'PHPUnit/GtkUI/Runner.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * 
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
class PHPUnit_GtkUI_SuiteTreeModel extends GtkTreeStore
{
    private $knownTests = array();
    private $controller;
        
    public function __construct($controller)
    {
        parent::__construct(
            Gtk::TYPE_PHP_VALUE,    // PHPUnit_Framework_Test
            Gtk::TYPE_BOOLEAN,      // Selected for run?
            Gtk::TYPE_DOUBLE        // PHPUnit_GtkUI_Runner::STATUS_*
            // Gtk::TYPE_STRING        // File the class resides in
        );

        $this->controller = $controller;

        $this->update();
    }

    public function update()
    {
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, 'PHPUnit_Framework_TestSuite')) {
                $this->addTest(null, new $class());
            }
        }
    }

    public function getTestIter(PHPUnit_Framework_Test $test)
    {
        $id = $test->toString();

        if (!isset($this->knownTests[$id])) {
            throw new RuntimeException("Test $id unknown!");
        } else {
            return $this->knownTests[$id];
        }
    }

    public function setTestStatus(GtkTreeIter $iter, $status)
    {
        $status = max($this->get_value($iter, 2), $status);
        $this->set($iter, 2, $status);

        if ($this->iter_depth($iter) !== 0) {
            $this->setTestStatus($this->iter_parent($iter), $status);
        }
    }

    public function showTestName($column, $cell, $model, $iter)
    {
        if (is_object($model->get_value($iter, 0))) {
            $name = $model->get_value($iter, 0)->toString();

            if (empty($name)) {
                $name = get_class($model->get_value($iter, 0));
            }
        } else {
            $name = $model->get_value($iter, 0);
        }

        $cell->set_property('text', $name);
    }

    public function showTestStausPixbuf($column, $cell, $model, $iter)
    {
        $cell->set_property(
            'pixbuf',
            PHPUnit_GtkUI_Controller_MainWindow::getStatusPixbuf(
                $model->get_value($iter, 2)
            )
        );
    }

    private function addTest($parentNode, $test, $parentStatus = NULL)
    {
        if (isset($this->knownTests[$test->toString()])) {
            return;
        }

        $testRow = $this->append(
          $parentNode,
          array($test, FALSE, PHPUnit_GtkUI_Runner::STATUS_NOTRUN)
        );

        $this->knownTests[$test->toString()] = $testRow;

        if ($test instanceof PHPUnit_Framework_TestSuite) {
            $parentStatus = PHPUnit_GtkUI_Controller_StatusTree::getInstance()->appendMessage(
              PHPUnit_GtkUI_Runner::STATUS_INFO,
              'Adding test suite ' . $test->getName(),
              $parentStatus
            );

            foreach($test->tests() as $subTest) {
                $this->addTest($testRow, $subTest, $parentStatus);
            }
        }
    }
}
?>
