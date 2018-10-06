<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

/**
 * A skipped test case
 */
class SkippedTestCase extends TestCase
{
    /**
     * @var string
     */
    protected $message = '';

    /**
     * @var boolean
     */
    protected $backupGlobals = false;

    /**
     * @var boolean
     */
    protected $backupStaticAttributes = false;

    /**
     * @var boolean
     */
    protected $runTestInSeparateProcess = false;

    /**
     * @var boolean
     */
    protected $useErrorHandler = false;

    /**
     * @var boolean
     */
    protected $useOutputBuffering = false;

    public function __construct(string $className, string $methodName, string $message = '')
    {
        parent::__construct($className . '::' . $methodName);

        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Returns a string representation of the test case.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function toString(): string
    {
        return $this->getName();
    }

    /**
     * @throws Exception
     */
    protected function runTest(): void
    {
        $this->markTestSkipped($this->message);
    }
}
