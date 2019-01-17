<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

use PHPUnit\Runner\BaseTestRunner;

class ExceptionInTestDetectedInTeardown extends TestCase
{
    public $exceptionDetected = false;

    protected function tearDown(): void
    {
        if (BaseTestRunner::STATUS_ERROR == $this->getStatus()) {
            $this->exceptionDetected = true;
        }
    }

    public function testSomething(): void
    {
        throw new Exception;
    }
}
