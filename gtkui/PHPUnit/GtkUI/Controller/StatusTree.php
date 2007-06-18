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

require_once 'PHPUnit/GtkUI/Controller/MainWindow.php';
require_once 'PHPUnit/GtkUI/Main.php';
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
class PHPUnit_GtkUI_Controller_StatusTree
{
    public $model;
    private $view;
    private $parent;
    private static $instance;

    public static function getInstance()
    {
        if (self::$instance === NULL) {
            self::$instance = new PHPUnit_GtkUI_Controller_StatusTree;
            self::$instance->init();
        }

        return self::$instance;
    }

    private function init()
    {
        $this->view  = PHPUnit_GtkUI_Main::getInstance()->glade->get_widget(
          'tvStatuses'
        );

        $this->model = new GtkTreeStore(
          Gtk::TYPE_DOUBLE, Gtk::TYPE_STRING, Gtk::TYPE_STRING
        );

        $this->view->set_model($this->model);

        $statusRenderer = new GtkCellRendererPixbuf;
        $statusRenderer->set_property('stock-size', Gtk::ICON_SIZE_SMALL_TOOLBAR);
        $this->view->insert_column_with_data_func(
          0, 'Status', $statusRenderer, array($this, 'showStatusPixbuf')
        );

        $dateRenderer = new GtkCellRendererText;
        $dateColumn   = new GtkTreeViewColumn('Date', $dateRenderer, 'text', 1);
        $this->view->append_column($dateColumn);

        $infoRenderer = new GtkCellRendererText;
        $infoColumn   = new GtkTreeViewColumn(
          'Status information', $infoRenderer, 'text', 2
        );

        $infoColumn->set_expand(TRUE);
        $this->view->append_column($infoColumn);
    }

    public function showStatusPixbuf($column, $cell, $model, $iter)
    {
        $cell->set_property(
          'pixbuf',
          PHPUnit_GtkUI_Controller_MainWindow::getStatusPixbuf(
            $model->get_value($iter, 0)
          )
        );
    }

    public function appendMessage($status, $message, $parent = NULL)
    {
        $iter = $this->model->append(
          $parent,
          array(
            $status,
            date('H:i:s'),
            $message
          )
        );

        if ($parent !== NULL) {
            $this->updateStatusesRecursive($parent, $status);
        }

        $this->view->scroll_to_cell(
          $this->model->get_path($iter)
        );

        PHPUnit_GtkUI_Main::processEvents();

        return $iter;
    }

    public function selectRow($status)
    {
        $this->view->get_selection()->unselect_all();
        $this->view->expand_to_path($this->model->get_path($status));
        $this->view->get_selection()->select_iter($status);
        $this->view->scroll_to_cell(
          $this->model->get_path($status), NULL, TRUE, 0.5, 0.5
        );

        PHPUnit_GtkUI_Main::processEvents();
    }

    private function updateStatusesRecursive($iter, $status)
    {
        if ($this->model->get_value($iter, 0) < $status) {
            $this->model->set($iter, 0, $status);

            PHPUnit_GtkUI_Main::processEvents();

            if ($this->model->iter_depth($iter) > 0) {
                $this->updateStatusesRecursive(
                  $this->model->iter_parent($iter), $status
                );
            }
        }
    }
}
?>
