<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration\CodeCoverage;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\XmlConfiguration\Exception;

#[CoversClass(CodeCoverage::class)]
#[Small]
#[Group('textui')]
#[Group('textui/configuration')]
#[Group('textui/configuration/xml')]
final class CodeCoverageTest extends TestCase
{
    public function testMayNotHaveDriver(): void
    {
        $codeCoverage = $this->codeCoverage();

        $this->assertFalse($codeCoverage->hasDriver());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Code Coverage driver has not been configured');

        $codeCoverage->driver();
    }

    public function testMayNotHaveCloverReport(): void
    {
        $codeCoverage = $this->codeCoverage();

        $this->assertFalse($codeCoverage->hasClover());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Code Coverage report "Clover XML" has not been configured');

        $codeCoverage->clover();
    }

    public function testMayNotHaveCoberturaReport(): void
    {
        $codeCoverage = $this->codeCoverage();

        $this->assertFalse($codeCoverage->hasCobertura());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Code Coverage report "Cobertura XML" has not been configured');

        $codeCoverage->cobertura();
    }

    public function testMayNotHaveCrap4jReport(): void
    {
        $codeCoverage = $this->codeCoverage();

        $this->assertFalse($codeCoverage->hasCrap4j());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Code Coverage report "Crap4J" has not been configured');

        $codeCoverage->crap4j();
    }

    public function testMayNotHaveHtmlReport(): void
    {
        $codeCoverage = $this->codeCoverage();

        $this->assertFalse($codeCoverage->hasHtml());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Code Coverage report "HTML" has not been configured');

        $codeCoverage->html();
    }

    public function testMayNotHaveOpenCloverReport(): void
    {
        $codeCoverage = $this->codeCoverage();

        $this->assertFalse($codeCoverage->hasOpenClover());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Code Coverage report "OpenClover XML" has not been configured');

        $codeCoverage->openClover();
    }

    public function testMayNotHavePhpReport(): void
    {
        $codeCoverage = $this->codeCoverage();

        $this->assertFalse($codeCoverage->hasPhp());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Code Coverage report "PHP" has not been configured');

        $codeCoverage->php();
    }

    public function testMayNotHaveTextReport(): void
    {
        $codeCoverage = $this->codeCoverage();

        $this->assertFalse($codeCoverage->hasText());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Code Coverage report "Text" has not been configured');

        $codeCoverage->text();
    }

    public function testMayNotHaveXmlReport(): void
    {
        $codeCoverage = $this->codeCoverage();

        $this->assertFalse($codeCoverage->hasXml());

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Code Coverage report "XML" has not been configured');

        $codeCoverage->xml();
    }

    private function codeCoverage(): CodeCoverage
    {
        return new CodeCoverage(
            null,
            false,
            false,
            false,
            false,
            false,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
        );
    }
}
