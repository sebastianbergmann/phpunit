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

use PHPUnit\Event\Code\IssueTrigger\Code;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
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

    public function testCountsHowOftenItWasTriggered(): void
    {
        $issue = Issue::from('file.php', 1, 'description', new Phpt('one.phpt'));

        $issue->triggeredBy(new Phpt('one.phpt'));
        $issue->triggeredBy(new Phpt('two.phpt'));

        $this->assertSame(3, $issue->numberOfTimesTriggered());
    }

    public function testDoesNotHaveTriggerTypesByDefault(): void
    {
        $issue = Issue::from('file.php', 1, 'description', new Phpt('test.phpt'));

        $issue->triggeredBy(new Phpt('test.phpt'));

        $this->assertSame([], $issue->triggerTypes());
    }

    public function testCountsHowOftenItWasTriggeredByEachTriggerType(): void
    {
        $test  = new Phpt('test.phpt');
        $issue = Issue::from('file.php', 1, 'description', $test, null, IssueTrigger::from(Code::ThirdParty, Code::ThirdParty));

        $issue->triggeredBy($test, IssueTrigger::from(Code::ThirdParty, Code::FirstParty));
        $issue->triggeredBy($test, IssueTrigger::from(Code::FirstParty, Code::ThirdParty));
        $issue->triggeredBy($test, IssueTrigger::from(Code::Test, Code::ThirdParty));
        $issue->triggeredBy($test, IssueTrigger::from(null, null));
        $issue->triggeredBy($test);

        $this->assertSame(['self' => 2, 'direct' => 1, 'indirect' => 1], $issue->triggerTypes());
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
