<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (!\defined('TEST_FILES_PATH')) {
    \define('TEST_FILES_PATH', __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR);
}

\ini_set('precision', 14);
\ini_set('serialize_precision', 14);

require_once __DIR__ . '/../vendor/autoload.php';

// TODO: Figure out why these are required (the classes should be autoloaded instead)
require_once TEST_FILES_PATH . 'BeforeAndAfterTest.php';

require_once TEST_FILES_PATH . 'BeforeClassAndAfterClassTest.php';

require_once TEST_FILES_PATH . 'TestWithTest.php';

require_once TEST_FILES_PATH . 'BeforeClassWithOnlyDataProviderTest.php';

require_once TEST_FILES_PATH . 'DataProviderSkippedTest.php';

require_once TEST_FILES_PATH . 'DataProviderDependencyTest.php';

require_once TEST_FILES_PATH . 'DataProviderIncompleteTest.php';

require_once TEST_FILES_PATH . 'InheritedTestCase.php';

require_once TEST_FILES_PATH . 'NoTestCaseClass.php';

require_once TEST_FILES_PATH . 'NoTestCases.php';

require_once TEST_FILES_PATH . 'NotPublicTestCase.php';

require_once TEST_FILES_PATH . 'NotVoidTestCase.php';

require_once TEST_FILES_PATH . 'OverrideTestCase.php';

require_once TEST_FILES_PATH . 'RequirementsClassBeforeClassHookTest.php';

require_once TEST_FILES_PATH . 'NoArgTestCaseTest.php';

require_once TEST_FILES_PATH . 'Singleton.php';
