<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Runner for PHPT test cases.
 *
 * @since Class available since Release 3.1.4
 */
class PHPUnit_Extensions_PhptTestCase implements PHPUnit_Framework_Test, PHPUnit_Framework_SelfDescribing
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var PHPUnit_Util_PHP
     */
    private $phpUtil;

    /**
     * @var array
     */
    private $settings = [
        'allow_url_fopen=1',
        'auto_append_file=',
        'auto_prepend_file=',
        'disable_functions=',
        'display_errors=1',
        'docref_root=',
        'docref_ext=.html',
        'error_append_string=',
        'error_prepend_string=',
        'error_reporting=-1',
        'html_errors=0',
        'log_errors=0',
        'magic_quotes_runtime=0',
        'output_handler=',
        'open_basedir=',
        'output_buffering=Off',
        'report_memleaks=0',
        'report_zend_debug=0',
        'safe_mode=0',
        'track_errors=1',
        'xdebug.default_enable=0'
    ];

    /**
     * Constructs a test case with the given filename.
     *
     * @param string           $filename
     * @param PHPUnit_Util_PHP $phpUtil
     *
     * @throws PHPUnit_Framework_Exception
     */
    public function __construct($filename, $phpUtil = null)
    {
        if (!is_string($filename)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!is_file($filename)) {
            throw new PHPUnit_Framework_Exception(
                sprintf(
                    'File "%s" does not exist.',
                    $filename
                )
            );
        }

        $this->filename = $filename;
        $this->phpUtil  = $phpUtil ?: PHPUnit_Util_PHP::factory();
    }

    /**
     * Counts the number of test cases executed by run(TestResult result).
     *
     * @return int
     */
    public function count()
    {
        return 1;
    }

    /**
     * @param array  $sections
     * @param string $output
     */
    private function assertPhptExpectation(array $sections, $output)
    {
        $assertions = [
            'EXPECT'      => 'assertEquals',
            'EXPECTF'     => 'assertStringMatchesFormat',
            'EXPECTREGEX' => 'assertRegExp',
        ];

        $actual = preg_replace('/\r\n/', "\n", trim($output));
        foreach ($assertions as $sectionName => $sectionAssertion) {
            if (isset($sections[$sectionName])) {
                $sectionContent = preg_replace('/\r\n/', "\n", trim($sections[$sectionName]));
                $assertion      = $sectionAssertion;
                $expected       = $sectionName == 'EXPECTREGEX' ? "/{$sectionContent}/" : $sectionContent;

                break;
            }
        }

        PHPUnit_Framework_Assert::$assertion($expected, $actual);
    }

    /**
     * Runs a test and collects its result in a TestResult instance.
     *
     * @param PHPUnit_Framework_TestResult $result
     *
     * @return PHPUnit_Framework_TestResult
     */
    public function run(PHPUnit_Framework_TestResult $result = null)
    {
        $sections = $this->parse();
        $code     = $this->render($sections['FILE']);

        if ($result === null) {
            $result = new PHPUnit_Framework_TestResult;
        }

        $skip     = false;
        $time     = 0;
        $settings = $this->settings;

        $result->startTest($this);

        if (isset($sections['INI'])) {
            $settings = array_merge($settings, $this->parseIniSection($sections['INI']));
        }

        // Redirects STDERR to STDOUT
        $this->phpUtil->setUseStderrRedirection(true);

        if (isset($sections['SKIPIF'])) {
            $jobResult = $this->phpUtil->runJob($sections['SKIPIF'], $settings);

            if (!strncasecmp('skip', ltrim($jobResult['stdout']), 4)) {
                if (preg_match('/^\s*skip\s*(.+)\s*/i', $jobResult['stdout'], $message)) {
                    $message = substr($message[1], 2);
                } else {
                    $message = '';
                }

                $result->addFailure($this, new PHPUnit_Framework_SkippedTestError($message), 0);

                $skip = true;
            }
        }

        if (!$skip) {
            PHP_Timer::start();

            $jobResult = $this->phpUtil->runJob($code, $settings);
            $time      = PHP_Timer::stop();

            try {
                $this->assertPhptExpectation($sections, $jobResult['stdout']);
            } catch (PHPUnit_Framework_AssertionFailedError $e) {
                $result->addFailure($this, $e, $time);
            } catch (Throwable $t) {
                $result->addError($this, $t, $time);
            } catch (Exception $e) {
                $result->addError($this, $e, $time);
            }

            if (isset($sections['CLEAN'])) {
                $cleanCode = $this->render($sections['CLEAN']);

                $this->phpUtil->runJob($cleanCode, $this->settings);
            }
        }

        $result->endTest($this, $time);

        return $result;
    }

    /**
     * Returns the name of the test case.
     *
     * @return string
     */
    public function getName()
    {
        return $this->toString();
    }

    /**
     * Returns a string representation of the test case.
     *
     * @return string
     */
    public function toString()
    {
        return $this->filename;
    }

    /**
     * @return array
     *
     * @throws PHPUnit_Framework_Exception
     */
    private function parse()
    {
        $sections = [];
        $section  = '';

        foreach (file($this->filename) as $line) {
            if (preg_match('/^--([_A-Z]+)--/', $line, $result)) {
                $section            = $result[1];
                $sections[$section] = '';

                continue;
            } elseif (empty($section)) {
                throw new PHPUnit_Framework_Exception('Invalid PHPT file');
            }

            $sections[$section] .= $line;
        }

        if (!isset($sections['FILE']) ||
            (!isset($sections['EXPECT']) && !isset($sections['EXPECTF']) && !isset($sections['EXPECTREGEX']))) {
            throw new PHPUnit_Framework_Exception('Invalid PHPT file');
        }

        return $sections;
    }

    /**
     * @param string $code
     *
     * @return string
     */
    private function render($code)
    {
        return str_replace(
            [
                '__DIR__',
                '__FILE__'
            ],
            [
                "'" . dirname($this->filename) . "'",
                "'" . $this->filename . "'"
            ],
            $code
        );
    }

    /**
     * Parse --INI-- section key value pairs and return as array.
     *
     * @param string
     *
     * @return array
     */
    protected function parseIniSection($content)
    {
        return preg_split('/\n|\r/', $content, -1, PREG_SPLIT_NO_EMPTY);
    }
}
