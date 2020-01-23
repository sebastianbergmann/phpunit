<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Configuration;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class IniSettingCollectionIterator implements \Countable, \Iterator
{
    /**
     * @var IniSetting[]
     */
    private $iniSettings;

    /**
     * @var int
     */
    private $position;

    public function __construct(IniSettingCollection $iniSettings)
    {
        $this->iniSettings = $iniSettings->asArray();
    }

    public function count(): int
    {
        return \iterator_count($this);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return $this->position < \count($this->iniSettings);
    }

    public function key(): int
    {
        return $this->position;
    }

    public function current(): IniSetting
    {
        return $this->iniSettings[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }
}
