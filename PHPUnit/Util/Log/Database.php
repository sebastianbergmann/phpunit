<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2009, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.1.0
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Runner/BaseTestRunner.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * Writes test result and code coverage data to a database.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2009 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.1.0
 */
class PHPUnit_Util_Log_Database implements PHPUnit_Framework_TestListener
{
    /**
     * @var    PHPUnit_Util_Log_Database
     */
    protected static $instance = NULL;

    /**
     * @var    integer
     */
    protected $currentTestId;

    /**
     * @var    integer
     */
    protected $runId;

    /**
     * @var    integer[]
     */
    protected $testSuites = array();

    /**
     * @var    boolean
     */
    protected $currentTestSuccess = TRUE;

    /**
     * @var    PDO
     */
    protected $dbh;

    /**
     * Constructor.
     *
     * @param  PDO     $dbh
     * @param  integer $revision
     * @param  string  $information
     * @throws PDOException
     * @throws RuntimeException
     */
    protected function __construct(PDO $dbh, $revision, $information = '')
    {
        $this->dbh = $dbh;

        $stmt = $this->dbh->prepare(
          'INSERT INTO run
                       (timestamp, revision, information)
                 VALUES(:timestamp, :revision, :information);'
        );

        $timestamp = time();

        $stmt->bindParam(':timestamp', $timestamp, PDO::PARAM_INT);
        $stmt->bindParam(':revision', $revision, PDO::PARAM_INT);
        $stmt->bindParam(':information', $information, PDO::PARAM_STR);
        $stmt->execute();

        $this->runId = $this->dbh->lastInsertId();
    }

    /**
     * @param  PDO     $dbh
     * @param  integer $revision
     * @param  string  $information
     * @return PHPUnit_Util_Log_Database
     * @throws InvalidArgumentException
     * @throws PDOException
     * @throws RuntimeException
     */
    public static function getInstance(PDO $dbh = NULL, $revision = '', $information = '')
    {
        if ($dbh === NULL) {
            if (self::$instance != NULL) {
                return self::$instance;
            } else {
                return FALSE;
            }
        }

        if (self::$instance != NULL) {
            throw new RuntimeException;
        }

        if (empty($revision)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'non-empty string');
        }

        self::$instance = new PHPUnit_Util_Log_Database(
          $dbh, $revision, $information
        );

        return self::$instance;
    }

    /**
     * Returns the ID of the current test.
     *
     * @return integer
     */
    public function getCurrentTestId()
    {
        return $this->currentTestId;
    }

    /**
     * Returns the ID of this test run.
     *
     * @return integer
     */
    public function getRunId()
    {
        return $this->runId;
    }

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $message = PHPUnit_Framework_TestFailure::exceptionToString($e) . "\n" .
                   PHPUnit_Util_Filter::getFilteredStacktrace($e, FALSE);

        $this->storeResult(
          PHPUnit_Runner_BaseTestRunner::STATUS_ERROR,
          $time,
          $message
        );

        $this->updateParents(
          $time, PHPUnit_Runner_BaseTestRunner::STATUS_ERROR
        );

        $this->currentTestSuccess = FALSE;
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $message = PHPUnit_Framework_TestFailure::exceptionToString($e) . "\n" .
                   PHPUnit_Util_Filter::getFilteredStacktrace($e, FALSE);

        $this->storeResult(
          PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE,
          $time,
          $message
        );

        $this->updateParents(
          $time, PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE
        );

        $this->currentTestSuccess = FALSE;
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $message = PHPUnit_Framework_TestFailure::exceptionToString($e) . "\n" .
                   PHPUnit_Util_Filter::getFilteredStacktrace($e, FALSE);

        $this->storeResult(
          PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE,
          $time,
          $message
        );

        $this->currentTestSuccess = FALSE;
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $message = PHPUnit_Framework_TestFailure::exceptionToString($e) . "\n" .
                   PHPUnit_Util_Filter::getFilteredStacktrace($e, FALSE);

        $this->storeResult(
          PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED,
          $time,
          $message
        );

        $this->currentTestSuccess = FALSE;
    }

    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if (empty($this->testSuites)) {
            $testSuiteId = $this->insertRootNode($suite->getName());
        } else {
            $testSuiteId = $this->insertNode($suite);
        }

        $this->testSuites[] = array(
          'id'     => $testSuiteId,
          'result' => PHPUnit_Runner_BaseTestRunner::STATUS_PASSED
        );
    }

    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        array_pop($this->testSuites);

        if (empty($this->testSuites)) {
            $stmt = $this->dbh->prepare(
              'UPDATE run
                  SET completed = 1
                WHERE run_id = :runId;'
            );

            $stmt->bindParam(':runId', $this->runId, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->insertNode($test);
        $this->currentTestSuccess = TRUE;
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if ($this->currentTestSuccess) {
            $this->storeResult(
              PHPUnit_Runner_BaseTestRunner::STATUS_PASSED, $time
            );

            $this->updateParents($time);
        }
    }

    /**
     * Inserts the root node into the tree.
     *
     * @param  string $name
     * @return integer
     * @throws PDOException
     */
    protected function insertRootNode($name)
    {
        $this->dbh->beginTransaction();

        $stmt = $this->dbh->prepare(
          'INSERT INTO test
                       (run_id, test_name, node_left, node_right, node_is_leaf,
                        node_parent, node_depth)
                 VALUES(:runId, :testName, 1, 2, 0, 0, 0);'
        );

        $stmt->bindParam(':runId', $this->runId, PDO::PARAM_INT);
        $stmt->bindParam(':testName', $name, PDO::PARAM_STR);
        $stmt->execute();

        $rootId = $this->dbh->lastInsertId();

        $stmt = $this->dbh->prepare(
          'UPDATE test
              SET node_root = :root
            WHERE test_id = :testId;'
        );

        $stmt->bindParam(':root', $rootId, PDO::PARAM_INT);
        $stmt->bindParam(':testId', $rootId, PDO::PARAM_INT);
        $stmt->execute();

        $this->dbh->commit();

        return $rootId;
    }

    /**
     * Inserts a node into the tree.
     *
     * @param  PHPUnit_Framework_Test $test
     * @throws PDOException
     */
    protected function insertNode(PHPUnit_Framework_Test $test)
    {
        $isLeaf = (int)!$test instanceof PHPUnit_Framework_TestSuite;

        $this->dbh->beginTransaction();

        $stmt = $this->dbh->prepare(
          'SELECT node_right
             FROM test
            WHERE test_id = :testId;'
        );

        $stmt->bindParam(':testId', $this->testSuites[count($this->testSuites)-1]['id'], PDO::PARAM_INT);
        $stmt->execute();

        $right = (int)$stmt->fetchColumn();
        unset($stmt);

        $stmt = $this->dbh->prepare(
          'UPDATE test
              SET node_left = node_left + 2
            WHERE node_root = :root
              AND node_left > :left;'
        );

        $stmt->bindParam(':root', $this->testSuites[0]['id'], PDO::PARAM_INT);
        $stmt->bindParam(':left', $right, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $this->dbh->prepare(
          'UPDATE test
              SET node_right  = node_right + 2
            WHERE node_root   = :root
              AND node_right >= :right;'
        );

        $stmt->bindParam(':root', $this->testSuites[0]['id'], PDO::PARAM_INT);
        $stmt->bindParam(':right', $right, PDO::PARAM_INT);
        $stmt->execute();

        $testName = $test->getName();
        $left     = $right;
        $right    = $right + 1;

        $stmt = $this->dbh->prepare(
          'INSERT INTO test
                       (run_id, test_name, test_result, test_message,
                        test_execution_time, node_root, node_parent, node_left,
                        node_right, node_depth, node_is_leaf)
                 VALUES(:runId, :testName, 0, "", 0, :root, :parent, :left, :right, :depth,
                        :isLeaf);'
        );

        $currentTestDepth = count($this->testSuites);

        $stmt->bindParam(':runId', $this->runId, PDO::PARAM_INT);
        $stmt->bindParam(':testName', $testName, PDO::PARAM_STR);
        $stmt->bindParam(':root', $this->testSuites[0]['id'], PDO::PARAM_INT);
        $stmt->bindParam(':parent', $this->testSuites[(count($this->testSuites)-1)]['id'], PDO::PARAM_INT);
        $stmt->bindParam(':left', $left, PDO::PARAM_INT);
        $stmt->bindParam(':right', $right, PDO::PARAM_INT);
        $stmt->bindPAram(':depth', $currentTestDepth, PDO::PARAM_INT);
        $stmt->bindParam(':isLeaf', $isLeaf, PDO::PARAM_INT);
        $stmt->execute();

        $this->currentTestId = $this->dbh->lastInsertId();
        $this->dbh->commit();

        if (!$test instanceof PHPUnit_Framework_TestSuite) {
            $test->__db_id = $this->currentTestId;
        }

        return $this->currentTestId;
    }

    /**
     * Stores a test result.
     *
     * @param  integer $result
     * @param  float   $time
     * @param  string  $message
     * @throws PDOException
     */
    protected function storeResult($result = PHPUnit_Runner_BaseTestRunner::STATUS_PASSED, $time = 0, $message = '')
    {
        $stmt = $this->dbh->prepare(
          'UPDATE test
              SET test_result         = :result,
                  test_message        = :message,
                  test_execution_time = :executionTime
            WHERE test_id             = :testId;'
        );

        $stmt->bindParam(':result', $result, PDO::PARAM_INT);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->bindParam(':executionTime', $time);
        $stmt->bindParam(':testId', $this->currentTestId, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * @param  float   $time
     * @param  integer $result
     * @throws PDOException
     */
    protected function updateParents($time, $result = NULL)
    {
        $stmtUpdateResultAndTime = $this->dbh->prepare(
          'UPDATE test
              SET test_result         = :result,
                  test_execution_time = test_execution_time + :time
            WHERE test_id             = :testSuiteId;'
        );

        $stmtUpdateTime = $this->dbh->prepare(
          'UPDATE test
              SET test_execution_time = test_execution_time + :time
            WHERE test_id             = :testSuiteId;'
        );

        foreach ($this->testSuites as &$testSuite) {
            if ($result > $testSuite['result']) {
                $stmtUpdateResultAndTime->bindParam(':result', $result, PDO::PARAM_INT);
                $stmtUpdateResultAndTime->bindParam(':testSuiteId', $testSuite['id'], PDO::PARAM_INT);
                $stmtUpdateResultAndTime->bindParam(':time', $time);
                $stmtUpdateResultAndTime->execute();

                $testSuite['result'] = $result;
            } else {
                $stmtUpdateTime->bindParam(':testSuiteId', $testSuite['id'], PDO::PARAM_INT);
                $stmtUpdateTime->bindParam(':time', $time);
                $stmtUpdateTime->execute();
            }
        }
    }
}
?>
