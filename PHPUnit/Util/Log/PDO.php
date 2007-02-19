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
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.1.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Util/CodeCoverage.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Writes test result and code coverage data to a database.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.1.0
 */
class PHPUnit_Util_Log_PDO implements PHPUnit_Framework_TestListener
{
    const schemaMySQL = '
CREATE TABLE IF NOT EXISTS run(
  run_id      INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  timestamp   INTEGER UNSIGNED NOT NULL,
  revision    INTEGER UNSIGNED NOT NULL,
  information TEXT             NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS test(
  run_id              INTEGER UNSIGNED NOT NULL REFERENCES run.run_id,
  test_id             INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  test_name           CHAR(128)        NOT NULL,
  test_result         TEXT             NOT NULL,
  test_execution_time FLOAT   UNSIGNED NOT NULL,
  node_root           INTEGER UNSIGNED NOT NULL,
  node_left           INTEGER UNSIGNED NOT NULL,
  node_right          INTEGER UNSIGNED NOT NULL,

  INDEX (run_id),
  INDEX (node_root),
  INDEX (node_left),
  INDEX (node_right)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS code_file(
  run_id         INTEGER UNSIGNED NOT NULL REFERENCES run.run_id,
  code_file_id   INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  code_file_name CHAR(255),

  INDEX (run_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS code_line(
  code_file_id      INTEGER UNSIGNED NOT NULL REFERENCES code_file.code_file_id,
  code_line_id      INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  code_line_number  INTEGER UNSIGNED NOT NULL,
  code_line         TEXT,
  code_line_covered TINYINT UNSIGNED NOT NULL,

  INDEX (code_file_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS code_coverage(
  test_id      INTEGER UNSIGNED NOT NULL REFERENCES test.test_id,
  code_line_id INTEGER UNSIGNED NOT NULL REFERENCES code_line.code_line_id,

  PRIMARY KEY (test_id, code_line_id)
) ENGINE=InnoDB;';

    const schemaSQLite = '
CREATE TABLE IF NOT EXISTS run(
  run_id      INTEGER PRIMARY KEY AUTOINCREMENT,
  timestamp   INTEGER,
  revision    INTEGER,
  information STRING
);

CREATE TABLE IF NOT EXISTS test(
  run_id              INTEGER,
  test_id             INTEGER PRIMARY KEY AUTOINCREMENT,
  test_name           TEXT,
  test_result         TEXT,
  test_execution_time REAL,
  node_root           INTEGER,
  node_left           INTEGER,
  node_right          INTEGER
);

CREATE INDEX IF NOT EXISTS test_run_id     ON test (run_id);
CREATE INDEX IF NOT EXISTS test_node_root  ON test (node_root);
CREATE INDEX IF NOT EXISTS test_node_left  ON test (node_left);
CREATE INDEX IF NOT EXISTS test_node_right ON test (node_right);

CREATE TABLE IF NOT EXISTS code_file(
  run_id         INTEGER,
  code_file_id   INTEGER PRIMARY KEY AUTOINCREMENT,
  code_file_name TEXT
);

CREATE INDEX IF NOT EXISTS code_file_run_id ON code_file (run_id);

CREATE TABLE IF NOT EXISTS code_line(
  code_file_id      INTEGER,
  code_line_id      INTEGER PRIMARY KEY AUTOINCREMENT,
  code_line_number  INTEGER,
  code_line         TEXT,
  code_line_covered INTEGER
);

CREATE INDEX IF NOT EXISTS code_line_code_file_id ON code_line (code_file_id);

CREATE TABLE IF NOT EXISTS code_coverage(
  test_id      INTEGER,
  code_line_id INTEGER
);

CREATE UNIQUE INDEX IF NOT EXISTS code_coverage_test_id_code_line_id ON code_coverage (test_id, code_line_id);';

    /**
     * @var    integer
     * @access protected
     */
    protected $runId;

    /**
     * @var    integer
     * @access protected
     */
    protected $rootId;

    /**
     * @var    integer[]
     * @access protected
     */
    protected $right = array();

    /**
     * @var    boolean
     * @access private
     */
    private $currentTestSuccess = TRUE;

    /**
     * Constructor.
     *
     * @param  string  $dsn
     * @param  integer $revision
     * @param  string  $information
     * @throws PDOException
     * @throws RuntimeException
     * @access public
     */
    public function __construct($dsn, $revision, $information = '')
    {
        $this->dbh = new PDO($dsn);

        switch ($this->dbh->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            case 'mysql': {
                $this->dbh->exec(self::schemaMySQL);
            }
            break;

            case 'sqlite': {
                $this->dbh->exec(self::schemaSQLite);
            }
            break;

            default: {
                throw new RuntimeException('Unsupported RDBMS.');
            }
        }

        $this->dbh->beginTransaction();

        $this->dbh->query(
          sprintf(
            'INSERT INTO run
                         (timestamp, revision, information)
                   VALUES(%d, %d, "%s");',

            time(),
            $revision,
            $information
          )
        );

        $this->runId = $this->dbh->lastInsertId();
    }

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->insertNode(
          $test,
          'Error: ' . $e->getMessage(),
          $time
        );

        $this->currentTestSuccess = FALSE;
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     * @access public
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->insertNode(
          $test,
          'Failure: ' . $e->getMessage(),
          $time
        );

        $this->currentTestSuccess = FALSE;
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->insertNode(
          $test,
          'Incomplete: ' . $e->getMessage(),
          $time
        );

        $this->currentTestSuccess = FALSE;
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @access public
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->insertNode(
          $test,
          'Skipped: ' . $e->getMessage(),
          $time
        );

        $this->currentTestSuccess = FALSE;
    }

    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @access public
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if (empty($this->right)) {
            $this->insertRootNode($suite->getName());
            array_push($this->right, 2);
        } else {
            array_push(
              $this->right,
              $this->insertNode($suite)
            );
        }
    }

    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @access public
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        array_pop($this->right);
    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     * @access public
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->currentTestSuccess = TRUE;
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     * @access public
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if ($this->currentTestSuccess) {
            $this->insertNode($test, 'OK', $time);
        }
    }

    /**
     * Stores code coverage information.
     *
     * @param  PHPUnit_Framework_TestResult $result
     * @access public
     */
    public function storeCodeCoverage(PHPUnit_Framework_TestResult $result)
    {
        $codeCoverage = $result->getCodeCoverageInformation(false, false);
        $summary      = PHPUnit_Util_CodeCoverage::getSummary($codeCoverage);
        $files        = array_keys($summary);

        foreach ($files as $file) {
            $this->dbh->query(
              sprintf(
                'INSERT INTO code_file
                             (run_id, code_file_name)
                       VALUES(%d, "%s");',

                $this->runId,
                $file
              )
            );

            $fileId = $this->dbh->lastInsertId();
            $i      = 1;
            $lines  = file($file);

            foreach ($lines as $line) {
                $covered = isset($summary[$file][$i]) ? $summary[$file][$i] : 0;

                $this->dbh->query(
                  sprintf(
                    'INSERT INTO code_line
                                 (code_file_id, code_line_number, code_line,
                                  code_line_covered)
                           VALUES(%d, %d, "%s", %d);',

                    $fileId,
                    $i,
                    trim($line),
                    $covered
                  )
                );

                if ($covered > 0) {
                    $lineId = $this->dbh->lastInsertId();

                    foreach ($codeCoverage as $test) {
                        if (isset($test['files'][$file][$i]) &&
                            $test['files'][$file][$i] > 0) {
                            $this->dbh->query(
                              sprintf(
                                'INSERT INTO code_coverage
                                             (test_id, code_line_id)
                                       VALUES(%d, %d);',

                                $test['test']->__db_id,
                                $lineId
                              )
                            );
                        }
                    }
                }

                $i++;
            }
        }

        $this->dbh->commit();
    }

    /**
     * Inserts the root node into the tree.
     *
     * @param  string $name
     * @throws PDOException
     * @access protected
     */
    protected function insertRootNode($name)
    {
        $this->dbh->query(
          sprintf(
            'INSERT INTO test
                         (run_id, test_name, node_left, node_right)
                   VALUES(%d, "%s", 1, 2);',

            $this->runId,
            $name
          )
        );

        $this->rootId = $this->dbh->lastInsertId();

        $this->dbh->query(
          sprintf(
            'UPDATE test
                SET node_root = %d
              WHERE test_id = %d;',

            $this->rootId,
            $this->rootId
          )
        );
    }

    /**
     * Inserts a node into the tree.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  string                 $result
     * @param  float                  $time
     * @return integer
     * @throws PDOException
     * @access protected
     */
    protected function insertNode(PHPUnit_Framework_Test $test, $result = '', $time = 0)
    {
        $right = $this->right[count($this->right)-1];

        $this->dbh->query(
          sprintf(
            'UPDATE test
                SET node_left = node_left + 2
              WHERE node_root = %d
                AND node_left > %d;',

            $this->rootId,
            $right
          )
        );

        $this->dbh->query(
          sprintf(
            'UPDATE test
                SET node_right  = node_right + 2
              WHERE node_root   = %d
                AND node_right >= %d;',

            $this->rootId,
            $right
          )
        );

        $this->dbh->query(
          sprintf(
            'INSERT INTO test
                         (run_id, test_name, test_result, test_execution_time,
                          node_root, node_left, node_right)
                   VALUES(%d, "%s", "%s", %f, %d, %d, %d);',

            $this->runId,
            $test->getName(),
            $result,
            $time,
            $this->rootId,
            $right,
            $right + 1
          )
        );

        if (!$test instanceof PHPUnit_Framework_TestSuite) {
            $test->__db_id = $this->dbh->lastInsertId();
        }

        return $right + 1;
    }
}
?>
