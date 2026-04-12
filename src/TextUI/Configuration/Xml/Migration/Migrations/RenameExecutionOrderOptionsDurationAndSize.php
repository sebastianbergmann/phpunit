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
use function explode;
use function implode;
use DOMDocument;
use DOMElement;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class RenameExecutionOrderOptionsDurationAndSize implements Migration
{
    public function migrate(DOMDocument $document): void
    {
        $root = $document->documentElement;

        assert($root instanceof DOMElement);

        if (!$root->hasAttribute('executionOrder')) {
            return;
        }

        $parts   = explode(',', $root->getAttribute('executionOrder'));
        $changed = false;

        foreach ($parts as &$part) {
            if ($part === 'duration') {
                $part    = 'duration-ascending';
                $changed = true;
            }

            if ($part === 'size') {
                $part    = 'size-ascending';
                $changed = true;
            }
        }

        unset($part);

        if ($changed) {
            $root->setAttribute('executionOrder', implode(',', $parts));
        }
    }
}
