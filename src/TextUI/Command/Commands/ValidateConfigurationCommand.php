<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Command;

use const PHP_EOL;
use function assert;
use function realpath;
use function sprintf;
use PHPUnit\Runner\Version;
use PHPUnit\TextUI\XmlConfiguration\CannotFindSchemaException;
use PHPUnit\TextUI\XmlConfiguration\SchemaFinder;
use PHPUnit\TextUI\XmlConfiguration\Validator;
use PHPUnit\Util\Xml\Loader as XmlLoader;
use PHPUnit\Util\Xml\XmlException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ValidateConfigurationCommand implements Command
{
    private string $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function execute(): Result
    {
        try {
            $document = (new XmlLoader)->loadFile($this->filename);
        } catch (XmlException $e) {
            return Result::from(
                sprintf(
                    'Cannot load XML configuration file %s:%s%s%s',
                    $this->filename,
                    PHP_EOL,
                    $e->getMessage(),
                    PHP_EOL,
                ),
                Result::FAILURE,
            );
        }

        try {
            $xsdFilename = (new SchemaFinder)->find(Version::series());
        } catch (CannotFindSchemaException $e) {
            return Result::from(
                sprintf(
                    'Cannot find schema for PHPUnit %s:%s%s%s',
                    Version::series(),
                    PHP_EOL,
                    $e->getMessage(),
                    PHP_EOL,
                ),
                Result::FAILURE,
            );
        }

        $validationResult = (new Validator)->validate($document, $xsdFilename);

        if (!$validationResult->hasValidationErrors()) {
            $configurationFileRealpath = realpath($this->filename);

            assert($configurationFileRealpath !== false && $configurationFileRealpath !== '');

            return Result::from(
                sprintf(
                    'XML configuration file %s is valid%s',
                    $configurationFileRealpath,
                    PHP_EOL,
                ),
            );
        }

        $configurationFileRealpath = realpath($this->filename);

        assert($configurationFileRealpath !== false && $configurationFileRealpath !== '');

        return Result::from(
            sprintf(
                'XML configuration file %s does not validate against the PHPUnit %s schema:%s%s',
                $configurationFileRealpath,
                Version::series(),
                PHP_EOL,
                $validationResult->asString(),
            ),
            Result::FAILURE,
        );
    }
}
