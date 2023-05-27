<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use function chr;
use function ord;
use function sprintf;
use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\XmlConfiguration\ValidationResult;

#[CoversClass(Xml::class)]
#[CoversClass(ValidationResult::class)]
#[Small]
final class XmlTest extends TestCase
{
    public static function charProvider(): array
    {
        $data = [];

        for ($i = 0; $i < 256; $i++) {
            $data[] = [chr($i)];
        }

        return $data;
    }

    #[DataProvider('charProvider')]
    public function testPrepareString(string $char): void
    {
        $e = null;

        $escapedString = Xml::prepareString($char);
        $xml           = "<?xml version='1.0' encoding='UTF-8' ?><tag>{$escapedString}</tag>";
        $dom           = new DOMDocument('1.0', 'UTF-8');

        try {
            $dom->loadXML($xml);
        } catch (Exception $e) {
        }

        $this->assertNull(
            $e,
            sprintf(
                '%s::prepareString("\x%02x") should not crash %s',
                Xml::class,
                ord($char),
                DOMDocument::class,
            ),
        );
    }
}
