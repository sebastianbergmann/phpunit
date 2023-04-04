<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\FilterErrorHandler;

final class VendorClass
{
    public function doSomething(): void
    {
        trigger_error('deprecation', \E_USER_DEPRECATED);
        trigger_error('notice', \E_USER_NOTICE);
        trigger_error('warning', \E_USER_WARNING);
    }
}
