<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestFailure;

class DirectoryExistsTest extends ConstraintTestCase
{
    public function testDefaults(): void
    {
        $constraint = new DirectoryExists();

        $this->assertCount(1, $constraint);
        $this->assertSame('directory exists', $constraint->toString());
    }

    public function testEvaluateReturnsFalseWhenDirectoryDoesNotExist(): void
    {
        $directory = __DIR__ . '/NonExistentDirectory';

        $constraint = new DirectoryExists();

        $this->assertFalse($constraint->evaluate($directory, '', true));
    }

    public function testEvaluateReturnsTrueWhenDirectoryExists(): void
    {
        $directory = __DIR__;

        $constraint = new DirectoryExists();

        $this->assertTrue($constraint->evaluate($directory, '', true));
    }

    public function testEvaluateThrowsExpectationFailedExceptionWhenDirectoryDoesNotExist(): void
    {
        $directory = __DIR__ . '/NonExistentDirectory';

        $constraint = new DirectoryExists();

        try {
            $constraint->evaluate($directory);
        } catch (ExpectationFailedException $e) {
            $this->assertSame(
                <<<PHP
Failed asserting that directory "$directory" exists.

PHP
                ,
                TestFailure::exceptionToString($e)
            );

            return;
        }

        $this->fail();
    }
}
