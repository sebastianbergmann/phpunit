<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration\Logging;

use PHPUnit\TextUI\XmlConfiguration\Exception;
use PHPUnit\TextUI\XmlConfiguration\Logging\TestDox\Html as TestDoxHtml;
use PHPUnit\TextUI\XmlConfiguration\Logging\TestDox\Text as TestDoxText;
use PHPUnit\TextUI\XmlConfiguration\Logging\TestDox\Xml as TestDoxXml;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @psalm-immutable
 */
final class Logging
{
    /**
     * @var ?Junit
     */
    private $junit;

    /**
     * @var ?Text
     */
    private $text;

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

    public function __construct(?Junit $junit, ?Text $text, ?TeamCity $teamCity, ?TestDoxHtml $testDoxHtml, ?TestDoxText $testDoxText, ?TestDoxXml $testDoxXml)
    {
        $this->junit       = $junit;
        $this->text        = $text;
        $this->teamCity    = $teamCity;
        $this->testDoxHtml = $testDoxHtml;
        $this->testDoxText = $testDoxText;
        $this->testDoxXml  = $testDoxXml;
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

    public function hasText(): bool
    {
        return $this->text !== null;
    }

    public function text(): Text
    {
        if ($this->text === null) {
            throw new Exception('Logger "Text" is not configured');
        }

        return $this->text;
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
