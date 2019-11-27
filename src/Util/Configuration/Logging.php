<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Configuration;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class Logging
{
    /**
     * @var ?CodeCoverageClover
     */
    private $codeCoverageClover;

    /**
     * @var ?CodeCoverageCrap4j
     */
    private $codeCoverageCrap4j;

    /**
     * @var ?CodeCoverageHtml
     */
    private $codeCoverageHtml;

    /**
     * @var ?CodeCoveragePhp
     */
    private $codeCoveragePhp;

    /**
     * @var ?CodeCoverageText
     */
    private $codeCoverageText;

    /**
     * @var ?CodeCoverageXml
     */
    private $codeCoverageXml;

    /**
     * @var ?Junit
     */
    private $junit;

    /**
     * @var ?PlainText
     */
    private $plainText;

    /**
     * @var ?TeamCity
     */
    private $teamCity;

    /**
     * @var ?TestDoxHtml
     */
    private $testDoxHtml;

    /**
     * @var ?TestDoxText
     */
    private $testDoxText;

    /**
     * @var ?TestDoxXml
     */
    private $testDoxXml;

    public function __construct(?CodeCoverageClover $codeCoverageClover, ?CodeCoverageCrap4j $codeCoverageCrap4j, ?CodeCoverageHtml $codeCoverageHtml, ?CodeCoveragePhp $codeCoveragePhp, ?CodeCoverageText $codeCoverageText, ?CodeCoverageXml $codeCoverageXml, ?Junit $junit, ?PlainText $plainText, ?TeamCity $teamCity, ?TestDoxHtml $testDoxHtml, ?TestDoxText $testDoxText, ?TestDoxXml $testDoxXml)
    {
        $this->codeCoverageClover = $codeCoverageClover;
        $this->codeCoverageCrap4j = $codeCoverageCrap4j;
        $this->codeCoverageHtml   = $codeCoverageHtml;
        $this->codeCoveragePhp    = $codeCoveragePhp;
        $this->codeCoverageText   = $codeCoverageText;
        $this->codeCoverageXml    = $codeCoverageXml;
        $this->junit              = $junit;
        $this->plainText          = $plainText;
        $this->teamCity           = $teamCity;
        $this->testDoxHtml        = $testDoxHtml;
        $this->testDoxText        = $testDoxText;
        $this->testDoxXml         = $testDoxXml;
    }

    public function hasCodeCoverageClover(): bool
    {
        return $this->codeCoverageClover !== null;
    }

    public function codeCoverageClover(): CodeCoverageClover
    {
        if ($this->codeCoverageClover === null) {
            throw new Exception('Logger "Clover XML" is not configured');
        }

        return $this->codeCoverageClover;
    }

    public function hasCodeCoverageCrap4j(): bool
    {
        return $this->codeCoverageCrap4j !== null;
    }

    public function codeCoverageCrap4j(): CodeCoverageCrap4j
    {
        if ($this->codeCoverageCrap4j === null) {
            throw new Exception('Logger "Crap4j XML" is not configured');
        }

        return $this->codeCoverageCrap4j;
    }

    public function hasCodeCoverageHtml(): bool
    {
        return $this->codeCoverageHtml !== null;
    }

    public function codeCoverageHtml(): CodeCoverageHtml
    {
        if ($this->codeCoverageHtml === null) {
            throw new Exception('Logger "Code Coverage HTML" is not configured');
        }

        return $this->codeCoverageHtml;
    }

    public function hasCodeCoveragePhp(): bool
    {
        return $this->codeCoveragePhp !== null;
    }

    public function codeCoveragePhp(): CodeCoveragePhp
    {
        if ($this->codeCoveragePhp === null) {
            throw new Exception('Logger "Code Coverage PHP" is not configured');
        }

        return $this->codeCoveragePhp;
    }

    public function hasCodeCoverageText(): bool
    {
        return $this->codeCoverageText !== null;
    }

    public function codeCoverageText(): CodeCoverageText
    {
        if ($this->codeCoverageText === null) {
            throw new Exception('Logger "Code Coverage Text" is not configured');
        }

        return $this->codeCoverageText;
    }

    public function hasCodeCoverageXml(): bool
    {
        return $this->codeCoverageXml !== null;
    }

    public function codeCoverageXml(): CodeCoverageXml
    {
        if ($this->codeCoverageXml === null) {
            throw new Exception('Logger "Code Coverage XML" is not configured');
        }

        return $this->codeCoverageXml;
    }

    public function hasJunit(): bool
    {
        return $this->junit !== null;
    }

    public function junit(): Junit
    {
        if ($this->junit === null) {
            throw new Exception('Logger "JUnit XML" is not configured');
        }

        return $this->junit;
    }

    public function hasPainText(): bool
    {
        return $this->plainText !== null;
    }

    public function plainText(): PlainText
    {
        if ($this->plainText === null) {
            throw new Exception('Logger "Plain Text" is not configured');
        }

        return $this->plainText;
    }

    public function hasTeamCity(): bool
    {
        return $this->teamCity !== null;
    }

    public function teamCity(): TeamCity
    {
        if ($this->teamCity === null) {
            throw new Exception('Logger "Team City" is not configured');
        }

        return $this->teamCity;
    }

    public function hasTestDoxHtml(): bool
    {
        return $this->testDoxHtml !== null;
    }

    public function testDoxHtml(): TestDoxHtml
    {
        if ($this->testDoxHtml === null) {
            throw new Exception('Logger "TestDox HTML" is not configured');
        }

        return $this->testDoxHtml;
    }

    public function hasTestDoxText(): bool
    {
        return $this->testDoxText !== null;
    }

    public function testDoxText(): TestDoxText
    {
        if ($this->testDoxText === null) {
            throw new Exception('Logger "TestDox Text" is not configured');
        }

        return $this->testDoxText;
    }

    public function hasTestDoxXml(): bool
    {
        return $this->testDoxXml !== null;
    }

    public function testDoxXml(): TestDoxXml
    {
        if ($this->testDoxXml === null) {
            throw new Exception('Logger "TestDox XML" is not configured');
        }

        return $this->testDoxXml;
    }
}
