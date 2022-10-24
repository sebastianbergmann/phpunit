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

use function chdir;
use function dirname;
use function error_reporting;
use function file_get_contents;
use function getcwd;
use function libxml_get_errors;
use function libxml_use_internal_errors;
use function sprintf;
use DOMDocument;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Loader
{
    /**
     * @throws XmlException
     */
    public function loadFile(string $filename, bool $isHtml = false, bool $xinclude = false, bool $strict = false): DOMDocument
    {
        $reporting = error_reporting(0);
        $contents  = file_get_contents($filename);

        error_reporting($reporting);

        if ($contents === false) {
            throw new XmlException(
                sprintf(
                    'Could not read "%s".',
                    $filename
                )
            );
        }

        return $this->load($contents, $isHtml, $filename, $xinclude, $strict);
    }

    /**
     * @throws XmlException
     */
    public function load(string $actual, bool $isHtml = false, string $filename = '', bool $xinclude = false, bool $strict = false): DOMDocument
    {
        if ($actual === '') {
            throw new XmlException('Could not load XML from empty string');
        }

        // Required for XInclude on Windows.
        if ($xinclude) {
            $cwd = getcwd();
            @chdir(dirname($filename));
        }

        $document                     = new DOMDocument;
        $document->preserveWhiteSpace = false;

        $internal  = libxml_use_internal_errors(true);
        $message   = '';
        $reporting = error_reporting(0);

        if ($filename !== '') {
            // Required for XInclude
            $document->documentURI = $filename;
        }

        if ($isHtml) {
            $loaded = $document->loadHTML($actual);
        } else {
            $loaded = $document->loadXML($actual);
        }

        if (!$isHtml && $xinclude) {
            $document->xinclude();
        }

        foreach (libxml_get_errors() as $error) {
            $message .= "\n" . $error->message;
        }

        libxml_use_internal_errors($internal);
        error_reporting($reporting);

        if (isset($cwd)) {
            @chdir($cwd);
        }

        if ($loaded === false || ($strict && $message !== '')) {
            if ($filename !== '') {
                throw new XmlException(
                    sprintf(
                        'Could not load "%s".%s',
                        $filename,
                        $message !== '' ? "\n" . $message : ''
                    )
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
