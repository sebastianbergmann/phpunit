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
use function assert;
use function max;
use function sprintf;
use function strlen;
use PHPUnit\Runner\PhpConfiguration\PhpConfigurationChecker;
use PHPUnit\Runner\Version;
use PHPUnit\Util\Color;
use SebastianBergmann\Environment\Console;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class CheckPhpConfigurationCommand implements Command
{
    private bool $colorize;

    public function __construct()
    {
        $this->colorize = (new Console)->hasColorSupport();
    }

    public function execute(): Result
    {
        $lines         = [];
        $shellExitCode = 0;

        foreach ((new PhpConfigurationChecker)->check() as $result) {
            if ($result->isOk()) {
                $check = $this->ok();
            } else {
                $check         = $this->notOk($result->actualValue());
                $shellExitCode = 1;
            }

            $lines[] = [
                sprintf(
                    '%s = %s',
                    $result->name(),
                    $result->valueForConfiguration(),
                ),
                $check,
            ];
        }

        $maxLength = 0;

        foreach ($lines as $line) {
            $maxLength = max($maxLength, strlen($line[0]));
        }

        $buffer = sprintf(
            'Checking whether PHP is configured according to https://docs.phpunit.de/en/%s/installation.html#configuring-php-for-development' . PHP_EOL . PHP_EOL,
            Version::series(),
        );

        foreach ($lines as $line) {
            $buffer .= sprintf(
                '%-' . $maxLength . 's ... %s' . PHP_EOL,
                $line[0],
                $line[1],
            );
        }

        return Result::from($buffer, $shellExitCode);
    }

    /**
     * @return non-empty-string
     */
    private function ok(): string
    {
        if (!$this->colorize) {
            return 'ok';
        }

        // @codeCoverageIgnoreStart
        $result = Color::colorizeTextBox('fg-green, bold', 'ok');

        assert($result !== '');

        return $result;
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return non-empty-string
     */
    private function notOk(string $actualValue): string
    {
        $message = sprintf('not ok (%s)', $actualValue);

        if (!$this->colorize) {
            return $message;
        }

        // @codeCoverageIgnoreStart
        $result = Color::colorizeTextBox('fg-red, bold', $message);

        assert($result !== '');

        return $result;
        // @codeCoverageIgnoreEnd
    }
}
