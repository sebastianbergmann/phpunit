<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util\Xml;

use function explode;
use function preg_match;
use function preg_match_all;
use function str_contains;
use function str_replace;
use function trim;
use DOMDocument;
use DOMNode;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Document extends DOMDocument
{
    public function loadXML(string $source, int $options = 0): bool
    {
        $this->preserveWhiteSpace = false;

        return parent::loadXML($source, $options);
    }

    public function saveXML(?DOMNode $node = null, int $options = 0): string
    {
        $this->formatOutput = true;

        $xml = parent::saveXML($node, $options);

        // find the first <phpunit> tag
        preg_match_all('#<phpunit[^>]*>#', $xml, $phpunitTags);

        $phpunitTag = $phpunitTags[0][0] ?? null;
        if (!$phpunitTag) {
            return $xml;
        }

        // Find all attributes within the <phpunit> tag
        preg_match('#(?<=<phpunit)(?:\s+([^\s=]+)="([^"]*)")*#', $phpunitTag, $attributes);

        if (!$attributes) {
            return $xml;
        }

        $phpunitNewTag = $phpunitTag;

        foreach (explode(' ', $attributes[0]) ?: [] as $attribute) {
            if (trim($attribute) === '' || str_contains($attribute, ':xsi=')) {
                // skip `xmlns:xsi` attribute
                continue;
            }

            $phpunitNewTag = str_replace(' ' . $attribute, PHP_EOL . '    ' . $attribute, $phpunitNewTag);
        }

        return str_replace($phpunitTag, $phpunitNewTag . PHP_EOL, $xml);
    }
}
