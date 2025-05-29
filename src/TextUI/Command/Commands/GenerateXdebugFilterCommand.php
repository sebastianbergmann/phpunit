<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Command;

use const PHP_EOL;
use PHPUnit\TextUI\Configuration\CodeCoverageFilterRegistry;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class GenerateXdebugFilterCommand implements Command
{
    /**
     * @var non-empty-string
     */
    private string $target;
    private Configuration $configuration;
    private CodeCoverageFilterRegistry $codeCoverageFilterRegistry;

    /**
     * @param non-empty-string $target
     */
    public function __construct(string $target, Configuration $configuration, CodeCoverageFilterRegistry $codeCoverageFilterRegistry)
    {
        $this->target                     = $target;
        $this->configuration              = $configuration;
        $this->codeCoverageFilterRegistry = $codeCoverageFilterRegistry;
    }

    public function execute(): Result
    {
        $this->codeCoverageFilterRegistry->init($this->configuration, true);

        if (!$this->codeCoverageFilterRegistry->configured()) {
            return Result::from(
                'Filter for code coverage has not been configured' . PHP_EOL,
                Result::FAILURE,
            );
        }

        file_put_contents(
            $this->target,
            sprintf(
                <<<'EOT'
<?php declare(strict_types=1);
if (!\function_exists('xdebug_set_filter')) {
    return;
}

\xdebug_set_filter(
    \XDEBUG_FILTER_CODE_COVERAGE,
    \XDEBUG_PATH_INCLUDE,
    [
%s
    ]
);

EOT,
                implode(
                    ",\n",
                    array_map(
                        static function (string $file): string
                        {
                            return sprintf(
                                "        '%s'",
                                $file
                            );
                        },
                        $this->codeCoverageFilterRegistry->get()->files()
                    )
                ),
            )
        );

        print 'Wrote Xdebug filter script to ' . $this->target . PHP_EOL;

        return Result::from();
    }
}
