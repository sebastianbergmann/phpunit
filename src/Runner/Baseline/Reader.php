<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Baseline;

use function assert;
use DOMElement;
use DOMXPath;
use PHPUnit\Util\Xml\Loader as XmlLoader;
use PHPUnit\Util\Xml\XmlException;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Reader
{
    /**
     * @throws CannotLoadBaselineException
     */
    public function read(string $source): Baseline
    {
        $baseline = new Baseline;

        try {
            $document = (new XmlLoader)->loadFile($source);
        } catch (XmlException $e) {
            throw new CannotLoadBaselineException(
                $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }

        $xpath = new DOMXPath($document);

        foreach ($xpath->query('file') as $fileElement) {
            assert($fileElement instanceof DOMElement);

            $file = $fileElement->getAttribute('path');

            foreach ($xpath->query('line', $fileElement) as $lineElement) {
                assert($lineElement instanceof DOMElement);

                $line = (int) $lineElement->getAttribute('number');
                $hash = $lineElement->getAttribute('hash');

                foreach ($xpath->query('issue', $lineElement) as $issueElement) {
                    assert($issueElement instanceof DOMElement);

                    $description = $issueElement->textContent;

                    assert(!empty($file));
                    assert($line > 0);
                    assert(!empty($hash));
                    assert(!empty($description));

                    $baseline->add(Issue::from($file, $line, $hash, $description));
                }
            }
        }

        return $baseline;
    }
}
