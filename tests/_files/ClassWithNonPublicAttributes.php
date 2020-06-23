<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

class ClassWithNonPublicAttributes extends ParentClassWithProtectedAttributes
{
    public static $publicStaticAttribute = 'foo';

    protected static $protectedStaticAttribute = 'bar';

    protected static $privateStaticAttribute = 'baz';

    public $publicAttribute = 'foo';

    public $foo = 1;

    public $bar = 2;

    public $publicArray = ['foo'];

    protected $protectedAttribute = 'bar';

    protected $privateAttribute = 'baz';

    protected $protectedArray = ['bar'];

    protected $privateArray = ['baz'];
}
