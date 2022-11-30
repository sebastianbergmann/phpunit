<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use SebastianBergmann\Template\Exception as TemplateException;
use SebastianBergmann\Template\Template;

/**
 * @internal This trait is not covered by the backward compatibility promise for PHPUnit
 */
trait TemplateLoader
{
    /**
     * @psalm-var array<string,Template>
     */
    private static array $templates = [];

    /**
     * @throws RuntimeException
     */
    private function loadTemplate(string $template): Template
    {
        $filename = __DIR__ . DIRECTORY_SEPARATOR . 'Generator' . DIRECTORY_SEPARATOR . $template;

        if (!isset(self::$templates[$filename])) {
            try {
                self::$templates[$filename] = new Template($filename);
            } catch (TemplateException $e) {
                throw new RuntimeException(
                    $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }
        }

        return self::$templates[$filename];
    }
}
