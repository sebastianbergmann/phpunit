<?php declare(strict_types=1);
namespace PHPUnit\TestFixture\SelfDirectIndirect;

@trigger_error('This class is deprecated', E_USER_DEPRECATED);

final class ThirdPartyClass
{
    const bool A = true;
}
