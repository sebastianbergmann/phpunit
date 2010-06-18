#!/usr/bin/env php
<?php
require_once 'PHPUnit/Framework/Assert.php';
require_once 'PHPUnit/Util/Class.php';
require_once 'Text/Template.php';

$class   = new ReflectionClass('PHPUnit_Framework_Assert');
$methods = array();

foreach ($class->getMethods() as $method) {
    $name = $method->getName();

    if (strpos($name, 'assert') === 0) {
        $methods[$name] = array(
          'docblock'  => $method->getDocComment(),
          'signature' => str_replace(
            array('= false', '= true'),
            array('= FALSE', '= TRUE'),
            PHPUnit_Util_Class::getMethodParameters($method)
          )
        );
    }
}

ksort($methods);

$buffer = '';

foreach ($methods as $name => $data) {
    $buffer .= sprintf(
      "\n\n%s\nfunction %s(%s)\n{\n    \$args = func_get_args();\n    call_user_func_array(array('PHPUnit_Framework_Assert', '%s'), \$args);\n}",
      str_replace('    ', '', $data['docblock']),
      $name,
      $data['signature'],
      $name
    );
}

$template = new Text_Template('PHPUnit/Framework/Assert/Functions.php.in');
$template->setVar(array('functions' => $buffer));
$template->renderTo('PHPUnit/Framework/Assert/Functions.php');
