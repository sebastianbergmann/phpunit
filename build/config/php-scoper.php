<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function replace_function(string $prefix, string $content, string $functionName): string {
    $incorrectUsage = sprintf('\\%s\\%s(', $prefix, $functionName);
    $correctUsage = sprintf('\\%s(', $functionName);

    return str_replace($incorrectUsage, $correctUsage, $content);

}

return [
    'whitelist' => [
        'PHPUnit\*',
    ],
    'patchers' => [
        function (string $filePath, string $prefix, string $content): string {
            return replace_function($prefix, $content, 'xdebug_info');
        },
    ]
];
