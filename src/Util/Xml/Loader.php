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

use function error_reporting;
use function file_get_contents;
use function libxml_get_errors;
use function libxml_use_internal_errors;
use function sprintf;
use DOMDocument;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Loader
{
    /**
     * @throws XmlException
     */
    public function loadFile(string $filename): DOMDocument
    {
        $reporting = error_reporting(0);
        $contents  = file_get_contents($filename);

        error_reporting($reporting);

        if ($contents === false) {
            throw new XmlException(
                sprintf(
                    'Could not read XML from file "%s"',
                    $filename,
                ),
            );
        }

        return $this->load($contents, $filename);
    }

    /**
     * @throws XmlException
     */
    public function load(string $actual, ?string $filename = null): DOMDocument
    {
        if ($actual === '') {
            if ($filename === null) {
                throw new XmlException('Could not parse XML from empty string');
            }

            throw new XmlException(
                sprintf(
                    'Could not parse XML from empty file "%s"',
                    $filename,
                ),
            );
        }

        $document                     = new Document;
        $document->preserveWhiteSpace = false;

        $internal  = libxml_use_internal_errors(true);
        $message   = '';
        $reporting = error_reporting(0);
        $loaded    = $document->loadXML($actual);

        foreach (libxml_get_errors() as $error) {
            $message .= "\n" . $error->message;
        }

        libxml_use_internal_errors($internal);
        error_reporting($reporting);

        if ($loaded === false || $message !== '') {
            if ($filename !== null) {
                throw new XmlException(
                    sprintf(
                        'Could not load "%s"%s',
                        $filename,
                        $message !== '' ? ":\n" . $message : '',
                    ),
                );
            }

            if ($message === '') {
                $message = 'Could not load XML for unknown reason';
            }

            throw new XmlException($message);
        }

        return $document;
    }
}
