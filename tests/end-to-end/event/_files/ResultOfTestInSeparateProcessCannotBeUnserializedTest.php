<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

interface ResultOfTestInSeparateProcessCannotBeUnserializedDependency
{
}

final readonly class ResultOfTestInSeparateProcessCannotBeUnserializedValue
{
    public ResultOfTestInSeparateProcessCannotBeUnserializedDependency $dependency;

    public function __construct(ResultOfTestInSeparateProcessCannotBeUnserializedDependency $dependency)
    {
        $this->dependency = $dependency;
    }
}

final class ResultOfTestInSeparateProcessCannotBeUnserializedTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testOne(): ResultOfTestInSeparateProcessCannotBeUnserializedValue
    {
        $this->assertTrue(true);

        return new ResultOfTestInSeparateProcessCannotBeUnserializedValue(
            $this->createStub(ResultOfTestInSeparateProcessCannotBeUnserializedDependency::class),
        );
    }
}
