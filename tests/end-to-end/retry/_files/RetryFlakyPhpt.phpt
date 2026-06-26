--TEST--
PHPT that fails on the first attempt and passes on the second
--FILE--
<?php declare(strict_types=1);
$marker = sys_get_temp_dir() . '/phpunit-e2e-phpt-retry.marker';

if (@file_get_contents($marker) === false) {
    file_put_contents($marker, '1');

    print 'FAIL';
} else {
    @unlink($marker);

    print 'OK';
}
--EXPECT--
OK
