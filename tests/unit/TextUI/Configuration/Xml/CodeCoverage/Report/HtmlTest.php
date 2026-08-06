<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\Configuration\Directory;
use PHPUnit\TextUI\Configuration\NoCustomCssFileException;
use PHPUnit\TextUI\Configuration\NoHtmlCoverageTargetException;

#[CoversClass(Html::class)]
#[Small]
#[Group('textui')]
#[Group('textui/configuration')]
#[Group('textui/configuration/xml')]
final class HtmlTest extends TestCase
{
    public function testMayNotHaveTarget(): void
    {
        $html = $this->html(null, null);

        $this->assertFalse($html->hasTarget());

        $this->expectException(NoHtmlCoverageTargetException::class);

        $html->target();
    }

    public function testMayHaveTarget(): void
    {
        $target = new Directory('/path/to/html-coverage');

        $html = $this->html($target, null);

        $this->assertTrue($html->hasTarget());
        $this->assertSame($target, $html->target());
    }

    public function testMayNotHaveCustomCssFile(): void
    {
        $html = $this->html(null, null);

        $this->assertFalse($html->hasCustomCssFile());

        $this->expectException(NoCustomCssFileException::class);

        $html->customCssFile();
    }

    public function testMayHaveCustomCssFile(): void
    {
        $html = $this->html(null, '/path/to/custom.css');

        $this->assertTrue($html->hasCustomCssFile());
        $this->assertSame('/path/to/custom.css', $html->customCssFile());
    }

    private function html(?Directory $target, ?string $customCssFile): Html
    {
        return new Html(
            $target,
            true,
            true,
            50,
            90,
            '#dff0d8',
            '#c3e3b5',
            '#f8f8f8',
            '#dddddd',
            '#f8f8f8',
            '#dddddd',
            '#99cb84',
            '#5fa544',
            '#fcf8e3',
            '#c09853',
            '#ffc20e',
            '#eeb32b',
            '#f2dede',
            '#a94442',
            '#c3272b',
            '#a11f22',
            '#6ca0dc',
            '#3b6ea5',
            $customCssFile,
        );
    }
}
