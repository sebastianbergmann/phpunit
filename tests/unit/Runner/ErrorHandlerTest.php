<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner;

use const E_DEPRECATED;
use const E_NOTICE;
use const E_USER_DEPRECATED;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use const E_WARNING;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(ErrorHandler::class)]
#[Small]
#[Group('test-runner')]
final class ErrorHandlerTest extends TestCase
{
    /**
     * @return array<string, array{int, non-empty-string}>
     */
    public static function nonTestCaseIssueProvider(): array
    {
        return [
            'E_NOTICE'          => [E_NOTICE, 'testRunnerTriggeredPhpNotice'],
            'E_USER_NOTICE'     => [E_USER_NOTICE, 'testRunnerTriggeredNotice'],
            'E_WARNING'         => [E_WARNING, 'testRunnerTriggeredPhpWarning'],
            'E_USER_WARNING'    => [E_USER_WARNING, 'testRunnerTriggeredWarning'],
            'E_DEPRECATED'      => [E_DEPRECATED, 'testRunnerTriggeredPhpDeprecation'],
            'E_USER_DEPRECATED' => [E_USER_DEPRECATED, 'testRunnerTriggeredDeprecation'],
        ];
    }

    public function testCanRegisterAndRestoreForNonTestCaseContext(): void
    {
        $errorHandler = ErrorHandler::instance();
        $errorHandler->registerForNonTestCaseContext();
        $errorHandler->restoreForNonTestCaseContext();

        $this->assertTrue(true);
    }

    /**
     * @param non-empty-string $eventMethod
     */
    #[DataProvider('nonTestCaseIssueProvider')]
    public function testEmitsEventForIssueTriggeredOutsideOfTestCaseContext(int $errorNumber, string $eventMethod): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method($eventMethod)
            ->with('message', __FILE__, 42, false, false)
            ->seal();

        $errorHandler = new ErrorHandler(false, $emitter);

        $this->assertTrue($errorHandler->handleNonTestCaseIssue($errorNumber, 'message', __FILE__, 42));
    }

    public function testDefersIssueTriggeredInTestCaseContext(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->never())
            ->method($this->anything())
            ->seal();

        $errorHandler = new ErrorHandler(false, $emitter);

        $errorHandler->enterTestCaseContext(self::class, 'testDefersIssueTriggeredInTestCaseContext');

        $this->assertTrue($errorHandler->handleNonTestCaseIssue(E_USER_WARNING, 'message', __FILE__, 42));

        $errorHandler->leaveTestCaseContext();
    }
}
