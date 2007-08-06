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
 * @since      File available since Release 3.1.4
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
 * @since      Class available since Release 3.1.4
 */
class PHPUnit_Util_Log_CodeCoverage_Database
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
     * @param  integer                      $revision
     * @access public
     */
    public function storeCodeCoverage(PHPUnit_Framework_TestResult $result, $revision)
    {
        $codeCoverage = $result->getCodeCoverageInformation(FALSE, TRUE);
        $summary      = PHPUnit_Util_CodeCoverage::getSummary($codeCoverage);
        $files        = array_keys($summary);
        $commonPath   = PHPUnit_Util_Filesystem::getCommonPath($files);

        $this->dbh->beginTransaction();

        foreach ($files as $file) {
            $filename = str_replace($commonPath, '', $file);
            $fileId   = FALSE;
            $lines    = file($file);
            $numLines = count($lines);

            $stmt = $this->dbh->prepare(
              'SELECT code_file_id
                 FROM code_file
                WHERE code_file_name = :filename
                  AND revision       = :revision;'
            );

            $stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
            $stmt->bindParam(':revision', $revision, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt) {
                $fileId = (int)$stmt->fetchColumn();
            }

            unset($stmt);

            if ($fileId == 0) {
                $hash = md5_file($file);

                $stmt = $this->dbh->prepare(
                  'INSERT INTO code_file
                               (code_file_name, code_file_md5, revision)
                         VALUES(:filename, :hash, :revision);'
                );

                $stmt->bindParam(':filename', $filename, PDO::PARAM_STR);
                $stmt->bindParam(':hash', $hash, PDO::PARAM_STR);
                $stmt->bindParam(':revision', $revision, PDO::PARAM_INT);
                $stmt->execute();

                $fileId  = $this->dbh->lastInsertId();
                $classes = PHPUnit_Util_Class::getClassesInFile($file, $commonPath);

                $stmt = $this->dbh->prepare(
                  'INSERT INTO code_class
                               (code_file_id, code_class_name,
                                code_class_start_line, code_class_end_line)
                         VALUES(:fileId, :className, :startLine, :endLine);'
                );

                foreach ($classes as $class) {
                    $className = $class->getName();
                    $startLine = $class->getStartLine();
                    $endLine   = $class->getEndLine();

                    $stmt->bindParam(':fileId', $fileId, PDO::PARAM_INT);
                    $stmt->bindParam(':className', $className, PDO::PARAM_STR);
                    $stmt->bindParam(':startLine', $startLine, PDO::PARAM_INT);
                    $stmt->bindParam(':endLine', $endLine, PDO::PARAM_INT);
                    $stmt->execute();

                    $classId = $this->dbh->lastInsertId();

                    $stmt2 = $this->dbh->prepare(
                      'INSERT INTO code_method
                                   (code_class_id, code_method_name,
                                    code_method_start_line, code_method_end_line)
                             VALUES(:classId, :methodName, :startLine, :endLine);'
                    );

                    foreach ($class->getMethods() as $method) {
                        if ($class->getName() != $method->getDeclaringClass()->getName()) {
                            continue;
                        }

                        $methodName = $method->getName();
                        $startLine  = $method->getStartLine();
                        $endLine    = $method->getEndLine();

                        $stmt2->bindParam(':classId', $classId, PDO::PARAM_INT);
                        $stmt2->bindParam(':methodName', $methodName, PDO::PARAM_STR);
                        $stmt2->bindParam(':startLine', $startLine, PDO::PARAM_INT);
                        $stmt2->bindParam(':endLine', $endLine, PDO::PARAM_INT);
                        $stmt2->execute();
                    }

                    unset($stmt2);
                }

                $stmt = $this->dbh->prepare(
                  'INSERT INTO code_line
                               (code_file_id, code_line_number, code_line,
                                code_line_covered)
                         VALUES(:fileId, :lineNumber, :line, :covered);'
                );

                $i = 1;

                foreach ($lines as $line) {
                    $covered = isset($summary[$file][$i]) ? 1 : 0;

                    $stmt->bindParam(':fileId', $fileId, PDO::PARAM_INT);
                    $stmt->bindParam(':lineNumber', $i, PDO::PARAM_INT);
                    $stmt->bindParam(':line', $line, PDO::PARAM_STR);
                    $stmt->bindParam(':covered', $covered, PDO::PARAM_INT);
                    $stmt->execute();

                    $i++;
                }
            }

            $stmt = $this->dbh->prepare(
              'SELECT code_line_id, code_line_covered
                 FROM code_line
                WHERE code_file_id     = :fileId
                  AND code_line_number = :lineNumber;'
            );

            $stmt2 = $this->dbh->prepare(
              'UPDATE code_line
                  SET code_line_covered = :lineCovered
                WHERE code_line_id      = :lineId;'
            );

            $stmt3 = $this->dbh->prepare(
              'INSERT INTO code_coverage
                      (test_id, code_line_id)
                VALUES(:testId, :lineId);'
            );

            for ($lineNumber = 1; $lineNumber <= $numLines; $lineNumber++) {
                $coveringTests = PHPUnit_Util_CodeCoverage::getCoveringTests(
                  $codeCoverage, $file, $lineNumber
                );

                if (is_array($coveringTests)) {
                    $stmt->bindParam(':fileId', $fileId, PDO::PARAM_INT);
                    $stmt->bindParam(':lineNumber', $lineNumber, PDO::PARAM_INT);
                    $stmt->execute();

                    $codeLineId      = (int)$stmt->fetchColumn(0);
                    $oldCoverageFlag = (int)$stmt->fetchColumn(1);
                    $newCoverageFlag = isset($summary[$file][$lineNumber]) ? 1 : 0;

                    if (($oldCoverageFlag == 0 && $newCoverageFlag != 0) ||
                        ($oldCoverageFlag <  0 && $newCoverageFlag >  0)) {
                        $stmt2->bindParam(':lineCovered', $newCoverageFlag, PDO::PARAM_INT);
                        $stmt2->bindParam(':lineId', $codeLineId, PDO::PARAM_INT);
                        $stmt2->execute();
                    }

                    foreach ($coveringTests as $test) {
                        $stmt3->bindParam(':testId', $test->__db_id, PDO::PARAM_INT);
                        $stmt3->bindParam(':lineId', $codeLineId, PDO::PARAM_INT);
                        $stmt3->execute();
                    }
                }
            }
        }

        unset($stmt);
        unset($stmt2);
        unset($stmt3);

        $stmt = $this->dbh->prepare(
          'SELECT code_method.code_method_id
             FROM code_class, code_method
            WHERE code_class.code_class_id     = code_method.code_class_id
              AND code_class.code_class_name   = :className
              AND code_method.code_method_name = :methodName;'
        );

        $stmt2 = $this->dbh->prepare(
          'UPDATE test
              SET code_method_id = :methodId
            WHERE test_id = :testId;'
        );

        foreach ($result->topTestSuite() as $test) {
            if ($test instanceof PHPUnit_Framework_TestCase) {
                $className  = get_class($test);
                $methodName = $test->getName();

                $stmt->bindParam(':className', $className, PDO::PARAM_STR);
                $stmt->bindParam(':methodName', $methodName, PDO::PARAM_STR);
                $stmt->execute();

                $methodId = (int)$stmt->fetchColumn();

                $stmt2->bindParam(':methodId', $methodId, PDO::PARAM_INT);
                $stmt2->bindParam(':testId', $test->__db_id, PDO::PARAM_INT);
                $stmt2->execute();
            }
        }

        unset($stmt);
        unset($stmt2);

        $this->dbh->commit();
    }
}
?>
