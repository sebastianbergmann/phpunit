<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
return [
    'whitelist' => [
        'PHPUnit\*',
    ],
    /**
     * @todo This can (and should) be removed once https://github.com/humbug/php-scoper/issues/540 is resolved
     */
    'patchers' => [
        static function (string $filePath, string $prefix, string $content): string
        {
            return scoper_replace_function($prefix, $content, 'xdebug_info');
        },
    ],
];

function scoper_replace_function(string $prefix, string $content, string $functionName): string
{
    $replacementsMap = [
        [
            sprintf('use function \\%s\\%s;', $prefix, $functionName),
            sprintf('use function \\%s;', $functionName),
        ],
        [
            sprintf('use function %s\\%s;', $prefix, $functionName),
            sprintf('use function %s;', $functionName),
        ],

        [
            sprintf('\\%s\\%s(', $prefix, $functionName),
            sprintf('\\%s(', $functionName),
        ],
        [
            sprintf(' %s\\%s(', $prefix, $functionName),
            sprintf(' %s(', $functionName),
        ],

        [
            sprintf('\'\\%s\\%s\'', $prefix, $functionName),
            sprintf('\'\\%s\'', $functionName),
        ],
        [
            sprintf('\'%s\\%s\'', $prefix, $functionName),
            sprintf('\'%s\'', $functionName),
        ],
        [
            sprintf('"\\%s\\%s"', $prefix, $functionName),
            sprintf('"\%s"', $functionName),
        ],
        [
            sprintf('"%s\\%s"', $prefix, $functionName),
            sprintf('"%s"', $functionName),
        ],
    ];

    foreach ($replacementsMap as [$incorrectPart, $correctPart]) {
        $content = str_replace($incorrectPart, $correctPart, $content);
    }

    return $content;
}
