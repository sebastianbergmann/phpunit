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
final class WarningTestCase extends TestCase
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
        $this->setBackupGlobals(false);
        $this->setBackupStaticProperties(false);
        $this->setRunClassInSeparateProcess(false);
        $this->setRunTestInSeparateProcess(false);

        $this->className  = $className;
        $this->methodName = $methodName;
        $this->message    = $message;

        parent::__construct('Warning');
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
        return 'Warning';
    }

    /**
     * @throws Exception
     *
     * @psalm-return never-return
     */
    protected function runTest(): mixed
    {
        throw new Warning($this->message);
    }
}
