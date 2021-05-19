<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Metadata\Parser;

use function array_slice;
use function file;
use function preg_match;
use function strtolower;
use ReflectionMethod;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class InlineAnnotationParser
{
    /**
     * @psalm-param class-string $className
     *
     * @psalm-return array<string, array{line: int, value: string}>
     */
    public function parse(string $className, string $methodName): array
    {
        $method      = new ReflectionMethod($className, $methodName);
        $lines       = file($method->getDeclaringClass()->getFileName());
        $lineNumber  = $method->getStartLine();
        $startLine   = $method->getStartLine() - 1;
        $endLine     = $method->getEndLine() - 1;
        $lines       = array_slice($lines, $startLine, $endLine - $startLine + 1);
        $annotations = [];

        foreach ($lines as $line) {
            if (preg_match('#/\*\*?\s*@(?P<name>[A-Za-z_-]+)(?:[ \t]+(?P<value>.*?))?[ \t]*\r?\*/$#m', $line, $matches)) {
                $annotations[strtolower($matches['name'])] = [
                    'line'  => $lineNumber,
                    'value' => $matches['value'],
                ];
            }

            $lineNumber++;
        }

        return $annotations;
    }
}
