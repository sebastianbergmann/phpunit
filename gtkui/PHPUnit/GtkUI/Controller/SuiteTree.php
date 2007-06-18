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
require_once 'PHPUnit/GtkUI/Main.php';
require_once 'PHPUnit/GtkUI/SuiteTreeModel.php';
require_once 'PHPUnit/GtkUI/TestStatusMapper.php';
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
class PHPUnit_GtkUI_Controller_SuiteTree
{
    public $model;
    
    private $view;
    private $parent;
    
    private static $instance;

    public function __get($attributeName)
    {
        switch ($attributeName) {
            case 'model': {
                return $this->model;
            }
            break;

            case 'view': {
                return $this->view;
            }
            break;
        }
    }

    public static function getInstance()
    {
        if (self::$instance === NULL) {
            self::$instance = new PHPUnit_GtkUI_Controller_SuiteTree;
            self::$instance->init();
        }

        return self::$instance;
    }

    private function init()
    {
        $this->view = PHPUnit_GtkUI_Main::getInstance()->glade->get_widget(
          'tvSuites'
        );

        $this->view->connect(
          'row-activated', array($this, 'selectTestStatus')
        );

        $this->model = new PHPUnit_GtkUI_SuiteTreeModel($this);
        $this->view->set_model($this->model);

        $nameRenderer = new GtkCellRendererText;

        $this->view->insert_column_with_data_func(
          0, 'Test suites', $nameRenderer, array($this->model, 'showTestName')
        );

        $nameColumn = $this->view->get_column(0);
        $nameColumn->set_expand(TRUE);
        
        $selectRenderer = new GtkCellRendererToggle;
        $selectRenderer->set_property('activatable', TRUE);
        $selectRenderer->connect(
          'toggled',
          array(
            $this,
            'toggleSelect',
          )
        );

        $selectColumn = new GtkTreeViewColumn(
          'Select for run', $selectRenderer, 'active', 1
        );
        
        $this->view->append_column($selectColumn);
        
        $statusRenderer = new GtkCellRendererPixbuf;
        $statusRenderer->set_property(
          'stock-size', Gtk::ICON_SIZE_SMALL_TOOLBAR
        );

        $this->view->insert_column_with_data_func(
          2, 'Test suites', $statusRenderer, array($this->model, 'showTestStausPixbuf')
        );
    }

    public function toggleSelect($renderer, $row)
    {
        $iter = $this->model->get_iter($row);
        $this->toggleRecursive($iter, !$this->model->get_value($iter, 1));
    }

    public function update()
    {
        $this->model->update();
    }

    public function markTest(PHPUnit_Framework_Test $test, $status)
    {
        $this->model->setTestStatus($this->model->getTestIter($test), $status);
    }

    private function toggleRecursive($iter, $value)
    {
        $this->model->set($iter, 1, $value);

        for ($i = 0; $i < $this->model->iter_n_children($iter); ++$i) {
            $this->toggleRecursive(
              $this->model->iter_nth_child($iter, $i),
              $value
           );
        }
    }

    public function selectTestStatus(GtkTreeView $view, array $path, GtkTreeViewColumn $column)
    {
        $model  = $view->get_model();
        $iter   = $model->get_iter($path);
        $status = PHPUnit_GtkUI_TestStatusMapper::getInstance()->getStatusIter(
            $model->get_value($iter, 0)
        );

        if ($status !== NULL) {
            PHPUnit_GtkUI_Controller_StatusTree::getInstance()->selectRow($status);
        } else {
            $this->view->expand_row($this->model->get_path($iter), FALSE);
        }
    }
}
?>
