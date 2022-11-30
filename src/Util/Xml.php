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

use const ENT_QUOTES;
use function assert;
use function class_exists;
use function htmlspecialchars;
use function mb_convert_encoding;
use function ord;
use function preg_replace;
use function settype;
use function strlen;
use DOMCharacterData;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use ReflectionClass;
use ReflectionException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Xml
{
    /**
     * @deprecated Only used by assertEqualXMLStructure()
     */
    public static function import(DOMElement $element): DOMElement
    {
        return (new DOMDocument)->importNode($element, true);
    }

    /**
     * @deprecated Only used by assertEqualXMLStructure()
     */
    public static function removeCharacterDataNodes(DOMNode $node): void
    {
        if ($node->hasChildNodes()) {
            for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
                if (($child = $node->childNodes->item($i)) instanceof DOMCharacterData) {
                    $node->removeChild($child);
                }
            }
        }
    }

    /**
     * Escapes a string for the use in XML documents.
     *
     * Any Unicode character is allowed, excluding the surrogate blocks, FFFE,
     * and FFFF (not even as character reference).
     *
     * @see https://www.w3.org/TR/xml/#charsets
     */
    public static function prepareString(string $string): string
    {
        return preg_replace(
            '/[\\x00-\\x08\\x0b\\x0c\\x0e-\\x1f\\x7f]/',
            '',
            htmlspecialchars(
                self::convertToUtf8($string),
                ENT_QUOTES
            )
        );
    }

    /**
     * "Convert" a DOMElement object into a PHP variable.
     */
    public static function xmlToVariable(DOMElement $element)
    {
        $variable = null;

        switch ($element->tagName) {
            case 'array':
                $variable = [];

                foreach ($element->childNodes as $entry) {
                    if (!$entry instanceof DOMElement || $entry->tagName !== 'element') {
                        continue;
                    }
                    $item = $entry->childNodes->item(0);

                    if ($item instanceof DOMText) {
                        $item = $entry->childNodes->item(1);
                    }

                    $value = self::xmlToVariable($item);

                    if ($entry->hasAttribute('key')) {
                        $variable[(string) $entry->getAttribute('key')] = $value;
                    } else {
                        $variable[] = $value;
                    }
                }

                break;

            case 'object':
                $className = $element->getAttribute('class');

                if ($element->hasChildNodes()) {
                    $arguments       = $element->childNodes->item(0)->childNodes;
                    $constructorArgs = [];

                    foreach ($arguments as $argument) {
                        if ($argument instanceof DOMElement) {
                            $constructorArgs[] = self::xmlToVariable($argument);
                        }
                    }

                    try {
                        assert(class_exists($className));

                        $variable = (new ReflectionClass($className))->newInstanceArgs($constructorArgs);
                        // @codeCoverageIgnoreStart
                    } catch (ReflectionException $e) {
                        throw new Exception(
                            $e->getMessage(),
                            $e->getCode(),
                            $e
                        );
                    }
                    // @codeCoverageIgnoreEnd
                } else {
                    $variable = new $className;
                }

                break;

            case 'boolean':
                $variable = $element->textContent === 'true';

                break;

            case 'integer':
            case 'double':
            case 'string':
                $variable = $element->textContent;

                settype($variable, $element->tagName);

                break;
        }

        return $variable;
    }

    private static function convertToUtf8(string $string): string
    {
        if (!self::isUtf8($string)) {
            $string = mb_convert_encoding($string, 'UTF-8');
        }

        return $string;
    }

    private static function isUtf8(string $string): bool
    {
        $length = strlen($string);

        for ($i = 0; $i < $length; $i++) {
            if (ord($string[$i]) < 0x80) {
                $n = 0;
            } elseif ((ord($string[$i]) & 0xE0) === 0xC0) {
                $n = 1;
            } elseif ((ord($string[$i]) & 0xF0) === 0xE0) {
                $n = 2;
            } elseif ((ord($string[$i]) & 0xF0) === 0xF0) {
                $n = 3;
            } else {
                return false;
            }

            for ($j = 0; $j < $n; $j++) {
                if ((++$i === $length) || ((ord($string[$i]) & 0xC0) !== 0x80)) {
                    return false;
                }
            }
        }

        return true;
    }
}
