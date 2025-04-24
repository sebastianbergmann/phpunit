<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use const PHP_EOL;
use function file_get_contents;
use function libxml_clear_errors;
use function libxml_get_errors;
use function libxml_use_internal_errors;
use function printf;
use function trim;
use function unlink;
use DOMDocument;

function validate_and_print(string $logfile): void
{
    libxml_use_internal_errors(true);

    $document = new DOMDocument;
    $document->load($logfile);

    if (!$document->schemaValidate(__DIR__ . '/schema/otr.xml')) {
        print 'Generated XML document does not validate against Open Test Reporting schemas:' . PHP_EOL . PHP_EOL;

        foreach (libxml_get_errors() as $error) {
            printf(
                '- Line %d: %s' . PHP_EOL,
                $error->line,
                trim($error->message),
            );
        }

        unset($error);

        print PHP_EOL;
    }

    libxml_clear_errors();

    unset($document);

    print file_get_contents($logfile);

    unlink($logfile);
}
