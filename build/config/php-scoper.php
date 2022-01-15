<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/scoper_replace_function.php';

return [
    'whitelist' => [
        'PHPUnit\*',
    ],
    'patchers' => [
        function (string $filePath, string $prefix, string $content): string {
            return scoper_replace_function($prefix, $content, 'xdebug_info');
        },
    ]
];
