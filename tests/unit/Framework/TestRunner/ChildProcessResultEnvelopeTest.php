<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\TestRunner;

use function serialize;
use PHPUnit\Event\EventCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestRunner\TestResult\PassedTests;

#[CoversClass(ChildProcessResultEnvelope::class)]
#[Small]
final class ChildProcessResultEnvelopeTest extends TestCase
{
    public function testStripsTheNonceThatPrefixesTheSerializedResult(): void
    {
        $this->assertSame(
            'the payload',
            ChildProcessResultEnvelope::verifyAndStripNonce('the noncethe payload', 'the nonce'),
        );
    }

    public function testPassesAResultThatIsNotExpectedToCarryANonceThrough(): void
    {
        $this->assertSame(
            'the payload',
            ChildProcessResultEnvelope::verifyAndStripNonce('the payload', null),
        );
    }

    public function testPassesAnEmptyResultThroughSoThatItsConsumerFailsOnTheEmptyPayload(): void
    {
        $this->assertSame('', ChildProcessResultEnvelope::verifyAndStripNonce('', 'the nonce'));
    }

    public function testRejectsAResultThatIsShorterThanTheNonceItShouldCarry(): void
    {
        $this->assertNull(ChildProcessResultEnvelope::verifyAndStripNonce('the', 'the nonce'));
    }

    public function testRejectsAResultThatDoesNotCarryTheNonceItShouldCarry(): void
    {
        // A result whose prefix does not match the nonce was written by an
        // unexpected process or was tampered with.
        $this->assertNull(
            ChildProcessResultEnvelope::verifyAndStripNonce('not the noncethe payload', 'the nonce'),
        );
    }

    public function testDecodesAResultThatCarriesTheEventsAndThePassedTests(): void
    {
        $decoded = ChildProcessResultEnvelope::decode(
            serialize((object) ['events' => new EventCollection, 'passedTests' => new PassedTests]),
        );

        $this->assertNotNull($decoded);
        $this->assertInstanceOf(EventCollection::class, $decoded->events);
        $this->assertInstanceOf(PassedTests::class, $decoded->passedTests);
    }

    public function testDoesNotDecodeAResultThatCannotBeUnserialized(): void
    {
        $this->assertNull(ChildProcessResultEnvelope::decode('this is not serialized data'));
    }

    public function testDoesNotDecodeAResultThatDoesNotCarryTheEvents(): void
    {
        $this->assertNull(
            ChildProcessResultEnvelope::decode(serialize((object) ['passedTests' => new PassedTests])),
        );
    }

    public function testDoesNotDecodeAResultThatDoesNotCarryThePassedTests(): void
    {
        $this->assertNull(
            ChildProcessResultEnvelope::decode(serialize((object) ['events' => new EventCollection])),
        );
    }

    public function testDoesNotDecodeAResultWhoseEventsAndPassedTestsAreOfTheWrongType(): void
    {
        $this->assertNull(
            ChildProcessResultEnvelope::decode(serialize((object) ['events' => 'events', 'passedTests' => 'passed tests'])),
        );
    }
}
