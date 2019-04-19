<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class TestProxyFixture
{
    public function returnString()
    {
        return 'result';
    }

    public function returnTypedString(): string
    {
        return 'result';
    }

    public function returnObject()
    {
        $result = new stdClass;

        $result->foo = 'bar';

        return $result;
    }

    public function returnTypedObject(): stdClass
    {
        $result = new stdClass;

        $result->foo = 'bar';

        return $result;
    }

    public function returnObjectOfFinalClass()
    {
        return new FinalClass('value');
    }

    public function returnTypedObjectOfFinalClass(): FinalClass
    {
        return new FinalClass('value');
    }
}
