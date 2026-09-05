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

#[CoversClass(DefaultTestImpactData::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/test-impact-analysis')]
final class DefaultTestImpactDataTest extends TestCase
{
    public function testRecordsNothingBeforeSomethingIsRecorded(): void
    {
        $this->assertSame([], (new DefaultTestImpactData)->recorded());
    }

    public function testRecordsTheFilesATestExecuted(): void
    {
        $data = new DefaultTestImpactData;

        $data->record('FooTest::testOne', ['/src/Foo.php', '/src/Bar.php']);
        $data->record('FooTest::testTwo', ['/src/Baz.php']);

        $this->assertSame(
            [
                'FooTest::testOne' => ['/src/Foo.php', '/src/Bar.php'],
                'FooTest::testTwo' => ['/src/Baz.php'],
            ],
            $data->recorded(),
        );
    }

    public function testKeepsWhatATestExecutedWhenItWasRunAgain(): void
    {
        $data = new DefaultTestImpactData;

        $data->record('FooTest::testOne', ['/src/Foo.php']);
        $data->record('FooTest::testOne', ['/src/Bar.php']);

        $this->assertSame(['FooTest::testOne' => ['/src/Bar.php']], $data->recorded());
    }
}
