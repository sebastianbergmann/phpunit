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
use function in_array;
use DOMDocument;
use DOMElement;

/**
 * The "defects" value of the executionOrder attribute used to be written before
 * the order it is applied on top of, even though it is applied after it. It now
 * has to be written last, so that the attribute reads in the order in which the
 * tests are reordered.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class MoveDefectsAfterOrderInExecutionOrder implements Migration
{
    public function migrate(DOMDocument $document): void
    {
        $root = $document->documentElement;

        assert($root instanceof DOMElement);

        if (!$root->hasAttribute('executionOrder')) {
            return;
        }

        $tokens = explode(',', $root->getAttribute('executionOrder'));

        if (!in_array('defects', $tokens, true)) {
            return;
        }

        $reordered = [];

        foreach ($tokens as $token) {
            if ($token !== 'defects') {
                $reordered[] = $token;
            }
        }

        $reordered[] = 'defects';

        if ($reordered === $tokens) {
            return;
        }

        $root->setAttribute('executionOrder', implode(',', $reordered));
    }
}
