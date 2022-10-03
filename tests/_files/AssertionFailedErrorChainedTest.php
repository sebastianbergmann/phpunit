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
use PHPUnit\Framework\AssertionFailedError;

class AssertionFailedErrorChainedTest extends TestCase
{
    protected function onNotSuccessfulTest(\Throwable $t): void {
      throw new AssertionFailedError($t->getMessage(), $t->getCode(), $t);
      parent::onNotSuccessfulTest($t);
    }

    public function testOne(): void
    {
        throw new \RuntimeException('foo');
    }
}
