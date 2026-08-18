--TEST--
A PHPT test that fails on its first attempt and passes on its second, for the tests of the retried PHPT unit
--FILE--
<?php declare(strict_types=1);
$marker = sys_get_temp_dir() . '/phpunit-parallel-phpt-retry.marker';

if (@file_get_contents($marker) === false) {
    file_put_contents($marker, '1');

    print 'the phpt test failed in a worker';
} else {
    @unlink($marker);

    print 'the phpt test ran in a worker';
}
--EXPECT--
the phpt test ran in a worker
