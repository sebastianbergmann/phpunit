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
 * A warning.
 *
 * @since Class available since Release 2.0.0
 */
class PHPUnit_Framework_WarningTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $message = '';

    /**
     * @var bool
     */
    protected $backupGlobals = false;

    /**
     * @var bool
     */
    protected $backupStaticAttributes = false;

    /**
     * @var bool
     */
    protected $runTestInSeparateProcess = false;

    /**
     * @var bool
     */
    protected $useErrorHandler = false;

    /**
     * @param string $message
     */
    public function __construct($message = '')
    {
        $this->message = $message;
        parent::__construct('Warning');
    }

    /**
     * @throws PHPUnit_Framework_Exception
     */
    protected function runTest()
    {
        throw new PHPUnit_Framework_Warning($this->message);
    }

    /**
     * @return string
     * @since  Method available since Release 3.0.0
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns a string representation of the test case.
     *
     * @return string
     * @since  Method available since Release 3.4.0
     */
    public function toString()
    {
        return 'Warning';
    }
}
