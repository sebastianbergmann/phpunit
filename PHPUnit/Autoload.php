<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2010, Sebastian Bergmann <sb@sebastian-bergmann.de>.
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
 * @copyright  2002-2010 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.5.0
 */

require_once 'PHP/CodeCoverage/Filter.php';

if (!function_exists('phpunit_autoload')) {
    function phpunit_autoload($class)
    {
        static $classes = array(
          'phpunit_textui_command' => '/TextUI/Command.php',
          'phpunit_textui_resultprinter' => '/TextUI/ResultPrinter.php',
          'phpunit_textui_testrunner' => '/TextUI/TestRunner.php',
          'phpunit_util_skeleton_test' => '/Util/Skeleton/Test.php',
          'phpunit_util_skeleton_class' => '/Util/Skeleton/Class.php',
          'phpunit_util_test' => '/Util/Test.php',
          'phpunit_util_log_xhprof' => '/Util/Log/XHProf.php',
          'phpunit_util_log_tap' => '/Util/Log/TAP.php',
          'phpunit_util_log_dbus' => '/Util/Log/DBUS.php',
          'phpunit_util_log_json' => '/Util/Log/JSON.php',
          'phpunit_util_log_junit' => '/Util/Log/JUnit.php',
          'phpunit_util_diff' => '/Util/Diff.php',
          'phpunit_util_file' => '/Util/File.php',
          'phpunit_util_type' => '/Util/Type.php',
          'phpunit_util_filesystem' => '/Util/Filesystem.php',
          'phpunit_util_invalidargumenthelper' => '/Util/InvalidArgumentHelper.php',
          'phpunit_util_testsuiteiterator' => '/Util/TestSuiteIterator.php',
          'phpunit_util_testdox_resultprinter_html' => '/Util/TestDox/ResultPrinter/HTML.php',
          'phpunit_util_testdox_resultprinter_text' => '/Util/TestDox/ResultPrinter/Text.php',
          'phpunit_util_testdox_nameprettifier' => '/Util/TestDox/NamePrettifier.php',
          'phpunit_util_testdox_resultprinter' => '/Util/TestDox/ResultPrinter.php',
          'phpunit_util_printer' => '/Util/Printer.php',
          'phpunit_util_configuration' => '/Util/Configuration.php',
          'phpunit_util_skeleton' => '/Util/Skeleton.php',
          'phpunit_util_getopt' => '/Util/Getopt.php',
          'phpunit_util_xml' => '/Util/XML.php',
          'phpunit_util_globalstate' => '/Util/GlobalState.php',
          'phpunit_util_errorhandler' => '/Util/ErrorHandler.php',
          'phpunit_util_class' => '/Util/Class.php',
          'phpunit_util_filter' => '/Util/Filter.php',
          'phpunit_util_fileloader' => '/Util/Fileloader.php',
          'phpunit_util_php' => '/Util/PHP.php',
          'phpunit_framework_test' => '/Framework/Test.php',
          'phpunit_framework_comparisonfailure' => '/Framework/ComparisonFailure.php',
          'phpunit_framework_constraint_stringstartswith' => '/Framework/Constraint/StringStartsWith.php',
          'phpunit_framework_constraint_isanything' => '/Framework/Constraint/IsAnything.php',
          'phpunit_framework_constraint_arrayhaskey' => '/Framework/Constraint/ArrayHasKey.php',
          'phpunit_framework_constraint_and' => '/Framework/Constraint/And.php',
          'phpunit_framework_constraint_attribute' => '/Framework/Constraint/Attribute.php',
          'phpunit_framework_constraint_isinstanceof' => '/Framework/Constraint/IsInstanceOf.php',
          'phpunit_framework_constraint_stringmatches' => '/Framework/Constraint/StringMatches.php',
          'phpunit_framework_constraint_isequal' => '/Framework/Constraint/IsEqual.php',
          'phpunit_framework_constraint_greaterthan' => '/Framework/Constraint/GreaterThan.php',
          'phpunit_framework_constraint_traversablecontainsonly' => '/Framework/Constraint/TraversableContainsOnly.php',
          'phpunit_framework_constraint_classhasstaticattribute' => '/Framework/Constraint/ClassHasStaticAttribute.php',
          'phpunit_framework_constraint_objecthasattribute' => '/Framework/Constraint/ObjectHasAttribute.php',
          'phpunit_framework_constraint_isidentical' => '/Framework/Constraint/IsIdentical.php',
          'phpunit_framework_constraint_stringendswith' => '/Framework/Constraint/StringEndsWith.php',
          'phpunit_framework_constraint_pcrematch' => '/Framework/Constraint/PCREMatch.php',
          'phpunit_framework_constraint_isnull' => '/Framework/Constraint/IsNull.php',
          'phpunit_framework_constraint_stringcontains' => '/Framework/Constraint/StringContains.php',
          'phpunit_framework_constraint_isempty' => '/Framework/Constraint/IsEmpty.php',
          'phpunit_framework_constraint_lessthan' => '/Framework/Constraint/LessThan.php',
          'phpunit_framework_constraint_not' => '/Framework/Constraint/Not.php',
          'phpunit_framework_constraint_istype' => '/Framework/Constraint/IsType.php',
          'phpunit_framework_constraint_istrue' => '/Framework/Constraint/IsTrue.php',
          'phpunit_framework_constraint_or' => '/Framework/Constraint/Or.php',
          'phpunit_framework_constraint_traversablecontains' => '/Framework/Constraint/TraversableContains.php',
          'phpunit_framework_constraint_xor' => '/Framework/Constraint/Xor.php',
          'phpunit_framework_constraint_isfalse' => '/Framework/Constraint/IsFalse.php',
          'phpunit_framework_constraint_classhasattribute' => '/Framework/Constraint/ClassHasAttribute.php',
          'phpunit_framework_constraint_fileexists' => '/Framework/Constraint/FileExists.php',
          'phpunit_framework_testfailure' => '/Framework/TestFailure.php',
          'phpunit_framework_skippedtesterror' => '/Framework/SkippedTestError.php',
          'phpunit_framework_incompletetesterror' => '/Framework/IncompleteTestError.php',
          'phpunit_framework_selfdescribing' => '/Framework/SelfDescribing.php',
          'phpunit_framework_comparisonfailure_type' => '/Framework/ComparisonFailure/Type.php',
          'phpunit_framework_comparisonfailure_object' => '/Framework/ComparisonFailure/Object.php',
          'phpunit_framework_comparisonfailure_scalar' => '/Framework/ComparisonFailure/Scalar.php',
          'phpunit_framework_comparisonfailure_string' => '/Framework/ComparisonFailure/String.php',
          'phpunit_framework_comparisonfailure_array' => '/Framework/ComparisonFailure/Array.php',
          'phpunit_framework_testlistener' => '/Framework/TestListener.php',
          'phpunit_framework_testresult' => '/Framework/TestResult.php',
          'phpunit_framework_expectationfailedexception' => '/Framework/ExpectationFailedException.php',
          'phpunit_framework_skippedtest' => '/Framework/SkippedTest.php',
          'phpunit_framework_exception' => '/Framework/Exception.php',
          'phpunit_framework_testsuite' => '/Framework/TestSuite.php',
          'phpunit_framework_error_notice' => '/Framework/Error/Notice.php',
          'phpunit_framework_error_warning' => '/Framework/Error/Warning.php',
          'phpunit_framework_warning' => '/Framework/Warning.php',
          'phpunit_framework_constraint' => '/Framework/Constraint.php',
          'phpunit_framework_testcase' => '/Framework/TestCase.php',
          'phpunit_framework_skippedtestsuiteerror' => '/Framework/SkippedTestSuiteError.php',
          'phpunit_framework_error' => '/Framework/Error.php',
          'phpunit_framework_testsuite_dataprovider' => '/Framework/TestSuite/DataProvider.php',
          'phpunit_framework_assertionfailederror' => '/Framework/AssertionFailedError.php',
          'phpunit_framework_incompletetest' => '/Framework/IncompleteTest.php',
          'phpunit_framework_assert' => '/Framework/Assert.php',
          'phpunit_runner_includepathtestcollector' => '/Runner/IncludePathTestCollector.php',
          'phpunit_runner_version' => '/Runner/Version.php',
          'phpunit_runner_basetestrunner' => '/Runner/BaseTestRunner.php',
          'phpunit_runner_standardtestsuiteloader' => '/Runner/StandardTestSuiteLoader.php',
          'phpunit_runner_testcollector' => '/Runner/TestCollector.php',
          'phpunit_runner_testsuiteloader' => '/Runner/TestSuiteLoader.php',
          'phpunit_extensions_grouptestsuite' => '/Extensions/GroupTestSuite.php',
          'phpunit_extensions_phpttestcase_logger' => '/Extensions/PhptTestCase/Logger.php',
          'phpunit_extensions_story_resultprinter_html' => '/Extensions/Story/ResultPrinter/HTML.php',
          'phpunit_extensions_story_resultprinter_text' => '/Extensions/Story/ResultPrinter/Text.php',
          'phpunit_extensions_story_given' => '/Extensions/Story/Given.php',
          'phpunit_extensions_story_step' => '/Extensions/Story/Step.php',
          'phpunit_extensions_story_scenario' => '/Extensions/Story/Scenario.php',
          'phpunit_extensions_story_resultprinter' => '/Extensions/Story/ResultPrinter.php',
          'phpunit_extensions_story_testcase' => '/Extensions/Story/TestCase.php',
          'phpunit_extensions_story_then' => '/Extensions/Story/Then.php',
          'phpunit_extensions_story_when' => '/Extensions/Story/When.php',
          'phpunit_extensions_outputtestcase' => '/Extensions/OutputTestCase.php',
          'phpunit_extensions_ticketlistener_github' => '/Extensions/TicketListener/GitHub.php',
          'phpunit_extensions_ticketlistener_googlecode' => '/Extensions/TicketListener/GoogleCode.php',
          'phpunit_extensions_phpttestsuite' => '/Extensions/PhptTestSuite.php',
          'phpunit_extensions_phpttestcase' => '/Extensions/PhptTestCase.php',
          'phpunit_extensions_testdecorator' => '/Extensions/TestDecorator.php',
          'phpunit_extensions_repeatedtest' => '/Extensions/RepeatedTest.php',
          'phpunit_extensions_ticketlistener' => '/Extensions/TicketListener.php'
        );

        $cn = strtolower($class);

        if (isset($classes[$cn])) {
            require __DIR__ . $classes[$cn];
        }
    }

    spl_autoload_register('phpunit_autoload');

    $dir    = dirname(__FILE__);
    $filter = PHP_CodeCoverage_Filter::getInstance();

    $filter->addDirectoryToBlacklist(
      $dir . '/Extensions', '.php', '', 'PHPUNIT', FALSE
    );

    $filter->addDirectoryToBlacklist(
      $dir . '/Framework', '.php', '', 'PHPUNIT', FALSE
    );

    $filter->addDirectoryToBlacklist(
      $dir . '/Runner', '.php', '', 'PHPUNIT', FALSE
    );

    $filter->addDirectoryToBlacklist(
      $dir . '/TextUI', '.php', '', 'PHPUNIT', FALSE
    );

    $filter->addDirectoryToBlacklist(
      $dir . '/Util', '.php', '', 'PHPUNIT', FALSE
    );

    $filter->addFileToBlacklist(__FILE__, 'PHPUNIT', FALSE);

    unset($dir, $filter);
}
