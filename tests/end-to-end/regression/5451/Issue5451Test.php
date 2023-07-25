<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue5451;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Issue5451Test extends TestCase
{
    public static function dataProviderThatTriggersPhpError(): array
    {
        $foo = [];
        $foo->bar();

        return [[]];
    }

    #[DataProvider('dataProviderThatTriggersPhpError')]
    public function testWithErrorInDataProvider(): void
    {
    }

    public function testThatWorks(): void
    {
        $this->assertTrue(true);
    }
}
