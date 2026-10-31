<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Phar;

use function define;
use Exception;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Filter;

final class StackTraceTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testDoesNotContainFramesOfCodeInThePhar(): void
    {
        // the exclude list is switched off, as it is for PHPUnit's own test
        // suite, so that frames of code in the PHAR are only left out because
        // they are in the PHAR
        define('PHPUNIT_TESTSUITE', true);

        $this->assertStringNotContainsString('phar://', Filter::stackTraceFromThrowableAsString(new Exception));
    }
}
