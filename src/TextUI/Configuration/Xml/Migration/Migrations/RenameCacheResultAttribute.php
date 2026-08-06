<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

use function assert;
use DOMDocument;
use DOMElement;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class RenameCacheResultAttribute implements Migration
{
    public function migrate(DOMDocument $document): void
    {
        $root = $document->documentElement;

        assert($root instanceof DOMElement);

        // @codeCoverageIgnoreStart
        if ($root->hasAttribute('recordTestRunHistory')) {
            return;
        }
        // @codeCoverageIgnoreEnd

        if (!$root->hasAttribute('cacheResult')) {
            return;
        }

        $root->setAttribute('recordTestRunHistory', $root->getAttribute('cacheResult'));
        $root->removeAttribute('cacheResult');
    }
}
