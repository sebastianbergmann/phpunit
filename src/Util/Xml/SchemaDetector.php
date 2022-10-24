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

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class SchemaDetector
{
    /**
     * @throws XmlException
     */
    public function detect(string $filename): SchemaDetectionResult
    {
        $document = (new Loader)->loadFile(
            $filename,
            false,
            true,
            true
        );

        foreach (['9.5', '9.2', '8.5'] as $candidate) {
            $schema = (new SchemaFinder)->find($candidate);

            if (!(new Validator)->validate($document, $schema)->hasValidationErrors()) {
                return new SuccessfulSchemaDetectionResult($candidate);
            }
        }

        return new FailedSchemaDetectionResult;
    }
}
