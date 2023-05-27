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

use function fgets;
use function file_put_contents;
use function getcwd;
use function trim;
use PHPUnit\Runner\Version;
use PHPUnit\TextUI\XmlConfiguration\Generator;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class GenerateConfigurationCommand implements Command
{
    public function execute(): Result
    {
        print 'Generating phpunit.xml in ' . getcwd() . PHP_EOL . PHP_EOL;
        print 'Bootstrap script (relative to path shown above; default: vendor/autoload.php): ';

        $bootstrapScript = $this->read();

        print 'Tests directory (relative to path shown above; default: tests): ';

        $testsDirectory = $this->read();

        print 'Source directory (relative to path shown above; default: src): ';

        $src = $this->read();

        print 'Cache directory (relative to path shown above; default: .phpunit.cache): ';

        $cacheDirectory = $this->read();

        if ($bootstrapScript === '') {
            $bootstrapScript = 'vendor/autoload.php';
        }

        if ($testsDirectory === '') {
            $testsDirectory = 'tests';
        }

        if ($src === '') {
            $src = 'src';
        }

        if ($cacheDirectory === '') {
            $cacheDirectory = '.phpunit.cache';
        }

        $generator = new Generator;

        file_put_contents(
            'phpunit.xml',
            $generator->generateDefaultConfiguration(
                Version::series(),
                $bootstrapScript,
                $testsDirectory,
                $src,
                $cacheDirectory,
            ),
        );

        /* @noinspection MissingDirectorySeparatorInspection */
        print PHP_EOL . 'Generated phpunit.xml in ' . getcwd() . '.' . PHP_EOL;
        print 'Make sure to exclude the ' . $cacheDirectory . ' directory from version control.' . PHP_EOL;

        return Result::from();
    }

    private function read(): string
    {
        return trim(fgets(STDIN));
    }
}
