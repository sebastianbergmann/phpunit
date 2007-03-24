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
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
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
require_once 'PHPUnit/Util/Class.php';
require_once 'PHPUnit/Util/CodeCoverage.php';
require_once 'PHPUnit/Util/Filesystem.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * 
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
class PHPUnit_Util_Database
{
    /**
     * @var    PDO
     * @access protected
     */
    protected $dbh;

    /**
     * Constructor.
     *
     * @param  PDO $dbh
     * @throws PDOException
     * @access public
     */
    public function __construct(PDO $dbh)
    {
        $this->dbh = $dbh;
    }

    /**
     * Stores code coverage information.
     *
     * @param  PHPUnit_Framework_TestResult $result
     * @param  integer                      $runId
     * @access public
     */
    public function storeCodeCoverage(PHPUnit_Framework_TestResult $result, $runId)
    {
        if (defined('PHPUnit_INSIDE_OWN_TESTSUITE')) {
            $filterPHPUnit = FALSE;
            xdebug_stop_code_coverage();
        } else {
            $filterPHPUnit = TRUE;
        }

        $codeCoverage = $result->getCodeCoverageInformation(FALSE, $filterPHPUnit);
        $summary      = PHPUnit_Util_CodeCoverage::getSummary($codeCoverage);
        $files        = array_keys($summary);
        $commonPath   = PHPUnit_Util_Filesystem::getCommonPath($files);

        foreach ($files as $file) {
            $this->dbh->beginTransaction();

            $this->dbh->exec(
              sprintf(
                'INSERT INTO code_file
                             (run_id, code_file_name, code_file_md5)
                       VALUES(%d, "%s", "%s");',

                $runId,
                str_replace($commonPath, '', $file),
                md5_file($file)
              )
            );

            $fileId    = $this->dbh->lastInsertId();
            $classes   = PHPUnit_Util_Class::getClassesInFile($file, $commonPath);
            $methodMap = array();

            foreach ($classes as $class) {
                $this->dbh->exec(
                  sprintf(
                    'INSERT INTO code_class
                                 (code_file_id, code_class_name,
                                  code_class_start_line, code_class_end_line)
                           VALUES(%d, "%s", %d, %d);',

                    $fileId,
                    $class->getName(),
                    $class->getStartLine(),
                    $class->getEndLine()
                  )
                );

                $classId = $this->dbh->lastInsertId();

                foreach ($class->getMethods() as $method) {
                    if ($class->getName() != $method->getDeclaringClass()->getName()) {
                        continue;
                    }

                    $startLine = $method->getStartLine();
                    $endLine   = $method->getEndLine();

                    $this->dbh->exec(
                      sprintf(
                        'INSERT INTO code_method
                                     (code_class_id, code_method_name,
                                      code_method_start_line, code_method_end_line)
                               VALUES(%d, "%s", %d, %d);',

                        $classId,
                        $method->getName(),
                        $startLine,
                        $endLine
                      )
                    );

                    $methodId = $this->dbh->lastInsertId();

                    for ($i = $startLine; $i <= $endLine; $i++) {
                        $methodMap[$i] = $methodId;
                    }
                }
            }

            $i      = 1;
            $lines  = file($file);

            foreach ($lines as $line) {
                $covered = isset($summary[$file][$i]) ? $summary[$file][$i] : 0;

                $this->dbh->exec(
                  sprintf(
                    'INSERT INTO code_line
                                 (code_file_id, code_method_id, code_line_number,
                                  code_line, code_line_covered)
                           VALUES(%d, %d, %d, "%s", %d);',

                    $fileId,
                    isset($methodMap[$i]) ? $methodMap[$i] : 0,
                    $i,
                    trim($line),
                    $covered
                  )
                );

                if ($covered > 0) {
                    $lineId = $this->dbh->lastInsertId();

                    $coveringTests = PHPUnit_Util_CodeCoverage::getCoveringTests(
                      $codeCoverage, $file, $i
                    );

                    if (is_array($coveringTests)) {
                        foreach ($coveringTests as $test) {
                            $this->dbh->exec(
                              sprintf(
                                'INSERT INTO code_coverage
                                             (test_id, code_line_id)
                                       VALUES(%d, %d);',

                                $test->__db_id,
                                $lineId
                              )
                            );
                        }
                    }
                }

                $i++;
            }

            foreach ($result->topTestSuite() as $test) {
                if ($test instanceof PHPUnit_Framework_TestCase) {
                    $stmt = $this->dbh->query(
                      sprintf(
                        'SELECT code_method.code_method_id
                           FROM code_class, code_method
                          WHERE code_class.code_class_id     = code_method.code_class_id
                            AND code_class.code_class_name   = "%s"
                            AND code_method.code_method_name = "%s";',

                        get_class($test),
                        $test->getName()
                      )
                    );

                    $methodId = (int)$stmt->fetchColumn();
                    unset($stmt);

                    $this->dbh->exec(
                      sprintf(
                        'UPDATE test
                            SET code_method_id = %d
                          WHERE test_id = %d;',

                        $methodId,
                        $test->__db_id
                      )
                    );
                }
            }

            $this->dbh->commit();
        }
    }
}
?>
