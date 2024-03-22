<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
\set_error_handler(static function (int $err_lvl, string $err_msg, string $err_file, int $err_line): bool
{
    throw new ErrorException($err_msg, 0, $err_lvl, $err_file, $err_line);
});
