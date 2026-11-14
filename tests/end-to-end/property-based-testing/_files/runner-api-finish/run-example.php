<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\PropertyBasedTesting\RunnerApi;

use const PHP_EOL;
use function chdir;
use PHPUnit\Runner\PropertyBased\AfterLastTestMethodFailedException;
use PHPUnit\Runner\PropertyBased\PropertyRunner;

require __DIR__ . '/../../../../bootstrap.php';

chdir(__DIR__);

// runs an example of the property, finishes its test class when asked to,
// and ends the process with the exit status
[, $property, $finish, $exitStatus] = $argv;

$phpunit = PropertyRunner::boot('phpunit.xml');

print $property . ': ' . $phpunit->replay(__NAMESPACE__ . '\\' . $property, [3], 1)['outcome'] . PHP_EOL;

if ($finish === 'finish') {
    try {
        $phpunit->finish();

        print 'finish()' . PHP_EOL;
    } catch (AfterLastTestMethodFailedException $e) {
        print 'finish(): ' . $e::class . PHP_EOL;
    }
}

exit((int) $exitStatus);
