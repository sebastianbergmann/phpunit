<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner\TestResult\Issues;

use PHPUnit\Event\Code\Phpt;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Issue::class)]
#[Small]
#[Group('test-runner')]
final class IssueTest extends TestCase
{
    public function testCountsHowOftenTheSameTestTriggeredIt(): void
    {
        $test  = new Phpt('test.phpt');
        $issue = Issue::from('file.php', 1, 'description', $test);

        $issue->triggeredBy($test);

        $this->assertSame(
            [
                $test->id() => [
                    'test'  => $test,
                    'count' => 2,
                ],
            ],
            $issue->triggeringTests(),
        );
    }

    public function testDoesNotHaveStackTraceByDefault(): void
    {
        $issue = Issue::from('file.php', 1, 'description', new Phpt('test.phpt'));

        $this->assertFalse($issue->hasStackTrace());
        $this->assertNull($issue->stackTrace());
    }

    public function testMayHaveStackTrace(): void
    {
        $issue = Issue::from('file.php', 1, 'description', new Phpt('test.phpt'), 'stack trace');

        $this->assertTrue($issue->hasStackTrace());
        $this->assertSame('stack trace', $issue->stackTrace());
    }
}
