<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (strpos(php_sapi_name(), 'cli') !== false) {
	eval('?>' . \file_get_contents('php://stdin'));
}
