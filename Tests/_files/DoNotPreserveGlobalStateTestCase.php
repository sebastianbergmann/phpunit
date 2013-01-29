<?php
/**
 * Test case to ensure that functionality when running in separate process while
 * NOT preserving global state works as intended.
 *
 * @author Charles Sprayberry
 */
class DoNotPreserveGlobalStateTestCase extends PHPUnit_Framework_TestCase
{

    protected $preserveGlobalState = FALSE;

    protected $runTestInSeparateProcess = TRUE;

    protected $inIsolation = TRUE;

    /**
     * @var Text_Template
     */
    protected $template;

    /**
     * Taking advantage of PHPUnit_Framework_TestCase::run() calling this method
     * to ensure we have access to the Text_Template created by the method.
     *
     * @param Text_Template $template
     */
    public function prepareTemplate(Text_Template $template)
    {
        $this->template = $template;
        parent::prepareTemplate($template);
    }

    /**
     * @return Text_Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

}
