<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\XmlConfiguration;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 * @psalm-immutable
 */
final class Php
{
    /**
     * @var DirectoryCollection
     */
    private $includePaths;

    /**
     * @var IniSettingCollection
     */
    private $iniSettings;

    /**
     * @var ConstantCollection
     */
    private $constants;

    /**
     * @var VariableCollection
     */
    private $globalVariables;

    /**
     * @var VariableCollection
     */
    private $envVariables;

    /**
     * @var VariableCollection
     */
    private $postVariables;

    /**
     * @var VariableCollection
     */
    private $getVariables;

    /**
     * @var VariableCollection
     */
    private $cookieVariables;

    /**
     * @var VariableCollection
     */
    private $serverVariables;

    /**
     * @var VariableCollection
     */
    private $filesVariables;

    /**
     * @var VariableCollection
     */
    private $requestVariables;

    public function __construct(DirectoryCollection $includePaths, IniSettingCollection $iniSettings, ConstantCollection $constants, VariableCollection $globalVariables, VariableCollection $envVariables, VariableCollection $postVariables, VariableCollection $getVariables, VariableCollection $cookieVariables, VariableCollection $serverVariables, VariableCollection $filesVariables, VariableCollection $requestVariables)
    {
        $this->includePaths     = $includePaths;
        $this->iniSettings      = $iniSettings;
        $this->constants        = $constants;
        $this->globalVariables  = $globalVariables;
        $this->envVariables     = $envVariables;
        $this->postVariables    = $postVariables;
        $this->getVariables     = $getVariables;
        $this->cookieVariables  = $cookieVariables;
        $this->serverVariables  = $serverVariables;
        $this->filesVariables   = $filesVariables;
        $this->requestVariables = $requestVariables;
    }

    public function includePaths(): DirectoryCollection
    {
        return $this->includePaths;
    }

    public function iniSettings(): IniSettingCollection
    {
        return $this->iniSettings;
    }

    public function constants(): ConstantCollection
    {
        return $this->constants;
    }

    public function globalVariables(): VariableCollection
    {
        return $this->globalVariables;
    }

    public function envVariables(): VariableCollection
    {
        return $this->envVariables;
    }

    public function postVariables(): VariableCollection
    {
        return $this->postVariables;
    }

    public function getVariables(): VariableCollection
    {
        return $this->getVariables;
    }

    public function cookieVariables(): VariableCollection
    {
        return $this->cookieVariables;
    }

    public function serverVariables(): VariableCollection
    {
        return $this->serverVariables;
    }

    public function filesVariables(): VariableCollection
    {
        return $this->filesVariables;
    }

    public function requestVariables(): VariableCollection
    {
        return $this->requestVariables;
    }
}
