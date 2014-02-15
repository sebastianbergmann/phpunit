#!/usr/bin/env php
<?php
$lock = json_decode(file_get_contents(__DIR__ . '/../composer.lock'));

foreach ($lock->packages as $package) {
    print $package->name . ': ' . $package->version;

    if (!preg_match('/^[v= ]*(([0-9]+)(\\.([0-9]+)(\\.([0-9]+)(-([0-9]+))?(-?([a-zA-Z-+][a-zA-Z0-9\\.\\-:]*)?)?)?)?)$/', $package->version)) {
        print '@' . $package->source->reference;
    }

    print "\n";
}

