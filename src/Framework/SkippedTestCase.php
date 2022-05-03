<?php declare(strict_types=1);
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
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class SkippedTestCase extends TestCase
{
    /**
     * @psalm-var class-string
     */
    private string $className;
    private string $methodName;
    private string $message;

    /**
     * @psalm-param class-string $className
     */
    public function __construct(string $className, string $methodName, string $message = '')
    {
        parent::__construct($className . '::' . $methodName);

        $this->setBackupGlobals(false);
        $this->setBackupStaticProperties(false);
        $this->setRunClassInSeparateProcess(false);
        $this->setRunTestInSeparateProcess(false);

        $this->className  = $className;
        $this->methodName = $methodName;
        $this->message    = $message;
    }

    /**
     * @psalm-return class-string
     */
    public function className(): string
    {
        return $this->className;
    }

    public function methodName(): string
    {
        return $this->methodName;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Returns a string representation of the test case.
     */
    public function toString(): string
    {
        return $this->getName();
    }

    /**
     * @throws Exception
     */
    protected function runTest(): never
    {
        $this->markTestSkipped($this->message);
    }
}
