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

require_once 'PHPUnit/GtkUI/Main.php';
require_once 'PHPUnit/GtkUI/FileFilter.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * 
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
class PHPUnit_GtkUI_Controller_FileChooser extends GtkDialog
{
    protected static $instance;
    protected $view;
    protected $fileList;
    protected $fileChooser;
    protected $filePattern;
    protected $searchProgress;
    protected $selectedFiles = array();

    public static function getInstance()
    {
        if (self::$instance === NULL) {
            self::$instance = new PHPUnit_GtkUI_Controller_FileChooser;
            self::$instance->init();
        }

        return self::$instance;
    }

    protected function init()
    {
        $main = PHPUnit_GtkUI_Main::getInstance();

        $this->view = $main->glade->get_widget('wndFileSearch');
        $this->fileChooser = $main->glade->get_widget('btnDirSelect');
        $this->filePattern = $main->glade->get_widget('txtFilePattern');
        $this->searchProgress = $main->glade->get_widget('prgFileSearch');

        $main->glade->get_widget('btnFileSearchFind')->connect(
          'clicked',
          array($this, 'findFiles')
        );

        $this->initFileList();
    }

    public function run()
    {
        $this->selectedFiles = array();

        if (($result = $this->view->run()) === Gtk::RESPONSE_OK) {
            $this->fileList->get_model()->foreach(
              array($this, 'determineSelectedFiles')
            );
        }

        return $result;
    }

    public function hide()
    {
        $this->view->hide();
    }

    public function getFilenames()
    {
        return $this->selectedFiles;
    }

    public function toggleSelect($renderer, $row)
    {
        $iter = $this->fileList->get_model()->get_iter($row);
        $this->toggleSelectRecursive($iter);
    }

    protected function toggleSelectRecursive($iter)
    {
        $this->fileList->get_model()->set(
          $iter, 1, !$this->fileList->get_model()->get_value($iter, 1)
        );

        for ($i = 0; $i < $this->fileList->get_model()->iter_n_children($iter); $i++) {
            $this->toggleSelectRecursive(
              $this->fileList->get_model()->iter_nth_child($iter, $i)
            );
        }
    }
    
    public function findFiles(GtkButton $btn)
    {
        $base    = $this->fileChooser->get_filename();
        $pattern = $this->filePattern->get_property('text');

        $this->fileList->get_model()->clear();

        $parent = $this->fileList->get_model()->append(
          NULL,
          array('All files found', FALSE)
        );

        $it = new PHPUnit_GtkUI_FileFilter(
          new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base)
          ),
          $pattern
        );

        foreach ($it as $file) {
            $this->searchProgress->pulse();

            $this->fileList->get_model()->append(
              $parent,
              array((string)$file, FALSE)
            );

            PHPUnit_GtkUI_Main::processEvents();
        }
    }

    public function determineSelectedFiles($store, $path, $iter )
    {
        if ($this->fileList->get_model()->get_value($iter, 1) === TRUE &&
            is_file($this->fileList->get_model()->get_value($iter, 0))) {
            $this->selectedFiles[] = $this->fileList->get_model()->get_value(
              $iter, 0
            );
        }
    }

    protected function initFileList()
    {
        $this->fileList = PHPUnit_GtkUI_Main::getInstance()->glade->get_widget(
          'tvFiles'
        );

        $model = new GtkTreeStore(Gtk::TYPE_STRING, Gtk::TYPE_BOOLEAN);
        $this->fileList->set_model($model);

        $fileRenderer = new GtkCellRendererText;
        $fileColumn   = new GtkTreeViewColumn('File', $fileRenderer, 'text', 0);
        $this->fileList->append_column($fileColumn);
        
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
          'Load', $selectRenderer, 'active', 1
        );

        $this->fileList->append_column($selectColumn);
    }
}
?>
