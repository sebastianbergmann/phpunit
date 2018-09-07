<?php
use PHPUnit\Framework\TestCase;

class Issue797Test extends TestCase
{
    protected $preserveGlobalState = false;

    public function testBootstrapPhpIsExecutedInIsolation()
    {
        $this->assertEquals(GITHUB_ISSUE, 797);
    }
}
