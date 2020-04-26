<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class SingletonClass
{
    public static function getInstance(): void
    {
    }

    protected function __construct()
    {
    }

    private function __sleep(): array
    {
    }

    private function __wakeup(): void
    {
    }

    private function __clone()
    {
    }

    public function doSomething(): void
    {
    }
}
