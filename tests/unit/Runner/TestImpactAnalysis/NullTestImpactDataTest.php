<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\TestImpactAnalysis;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(NullTestImpactData::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class NullTestImpactDataTest extends TestCase
{
    public function testDoesNotRecordWhatATestExecuted(): void
    {
        $data = new NullTestImpactData;

        $data->record('FooTest::testOne', ['/src/Foo.php']);

        $this->assertSame([], $data->recorded());
    }
}
