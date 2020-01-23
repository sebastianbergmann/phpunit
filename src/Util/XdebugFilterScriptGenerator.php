<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use PHPUnit\TextUI\Configuration\Filter as FilterConfiguration;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class XdebugFilterScriptGenerator
{
    public function generate(FilterConfiguration $filter): string
    {
        $files = \array_map(
            static function ($item) {
                return \sprintf(
                    "        '%s'",
                    $item
                );
            },
            $this->getWhitelistItems($filter)
        );

        $files = \implode(",\n", $files);

        return <<<EOF
<?php declare(strict_types=1);
if (!\\function_exists('xdebug_set_filter')) {
    return;
}

\\xdebug_set_filter(
    \\XDEBUG_FILTER_CODE_COVERAGE,
    \\XDEBUG_PATH_WHITELIST,
    [
$files
    ]
);

EOF;
    }

    private function getWhitelistItems(FilterConfiguration $filter): array
    {
        $files = [];

        foreach ($filter->directories() as $directory) {
            $path = \realpath($directory->path());

            if (\is_string($path)) {
                $files[] = \sprintf(
                    \addslashes('%s' . \DIRECTORY_SEPARATOR),
                    $path
                );
            }
        }

        foreach ($filter->files() as $file) {
            $files[] = $file->path();
        }

        return $files;
    }
}
