--TEST--
Order by modification time descending: Suite with PHPT tests whose files were modified at different times
--FILE--
<?php declare(strict_types=1);
$sandbox = sys_get_temp_dir() . '/' . basename(__FILE__, '.phpt');

@mkdir($sandbox);

/*
 * A PHPT test is its file, so the fixtures are written to a directory of their
 * own, where their modification times can be set explicitly.
 */
$modificationTimes = [
    'old'    => 1704067200,
    'middle' => 1735689600,
    'new'    => 1767225600,
];

foreach ($modificationTimes as $name => $modificationTime) {
    file_put_contents(
        $sandbox . '/' . $name . '.phpt',
        <<<PHPT
        --TEST--
        {$name}
        --FILE--
        <?php declare(strict_types=1);
        print '{$name}';
        --EXPECT--
        {$name}
        PHPT
    );

    touch($sandbox . '/' . $name . '.phpt', $modificationTime);
}

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--order-by';
$_SERVER['argv'][] = 'modified-descending';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = $sandbox;

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
$sandbox = sys_get_temp_dir() . '/' . basename(__FILE__, '.phpt');

foreach (glob($sandbox . '/*.phpt') as $file) {
    unlink($file);
}

rmdir($sandbox);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (CLI Arguments, 3 tests)
Test Preparation Started (%s%enew.phpt)
Test Prepared (%s%enew.phpt)
Child Process Started (FILE section of a PHPT test)
Child Process Finished (FILE section of a PHPT test)
Test Passed (%s%enew.phpt)
Test Finished (%s%enew.phpt)
Test Preparation Started (%s%emiddle.phpt)
Test Prepared (%s%emiddle.phpt)
Child Process Started (FILE section of a PHPT test)
Child Process Finished (FILE section of a PHPT test)
Test Passed (%s%emiddle.phpt)
Test Finished (%s%emiddle.phpt)
Test Preparation Started (%s%eold.phpt)
Test Prepared (%s%eold.phpt)
Child Process Started (FILE section of a PHPT test)
Child Process Finished (FILE section of a PHPT test)
Test Passed (%s%eold.phpt)
Test Finished (%s%eold.phpt)
Test Suite Finished (CLI Arguments, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
