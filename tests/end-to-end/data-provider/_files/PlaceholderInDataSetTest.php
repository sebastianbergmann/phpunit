<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class PlaceholderInDataSetTest extends TestCase
{
    /**
     * The values look like the placeholders of the template from which the
     * code of the child process is rendered.
     *
     * @return non-empty-list<array{non-empty-string, non-empty-string}>
     */
    public static function provider(): array
    {
        return [
            ['{name}', 'name'],
            ['{filename}', 'filename'],
            ['{processResultFile}', 'processResultFile'],
            ['{serializedConfiguration}', 'serializedConfiguration'],
        ];
    }

    #[DataProvider('provider')]
    #[RunInSeparateProcess]
    public function testReceivesTheDataSetUnchanged(string $value, string $placeholder): void
    {
        $this->assertSame('{' . $placeholder . '}', $value);
    }
}
