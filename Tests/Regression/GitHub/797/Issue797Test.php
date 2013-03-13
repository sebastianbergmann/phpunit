<?php


/**
 * Ensures that the code template for code running in separate process will include
 * the configured bootstrap regardless of preserving global state.
 *
 * @author Charles Sprayberry
 */
class Issue797Test extends PHPUnit_Framework_TestCase
{

    public function run(PHPUnit_Framework_TestResult $result = null)
    {
        // although we have marked to run in isolation we must set this explicitly
        // or first assertion in test is not valid.
        $this->setRunTestInSeparateProcess(TRUE);
        $this->setIncludeBootstrap(TRUE);
        $GLOBALS['__PHPUNIT_BOOTSTRAP'] = __DIR__ . '/bootstrap.php';
        parent::run($result);
    }

    public function testRunningInSeparateProcessIncludesBootstrapPath()
    {
        $this->assertTrue($this->runTestInSeparateProcess, 'This test is not being ran in a separate process');
        $this->assertSame('797', GITHUB_ISSUE_797, 'The constant created by test bootstrap was not ran in isolation process');
    }

}
