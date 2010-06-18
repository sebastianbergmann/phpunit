#!/usr/bin/env php
<?php
require_once 'PHPUnit/Framework/Assert.php';
require_once 'PHPUnit/Util/Class.php';
require_once 'Text/Template.php';

$class   = new ReflectionClass('PHPUnit_Framework_Assert');
$methods = array();

foreach ($class->getMethods() as $method) {
    $docblock = $method->getDocComment();
    $name     = $method->getName();

    if (strpos($name, 'assert') === 0 ||
        strpos($docblock, '@return PHPUnit_Framework_Constraint') !== 0) {
        $methods[$name] = array(
          'docblock' => $docblock,
          'sigDecl'  => str_replace(
            array('= false', '= true'),
            array('= FALSE', '= TRUE'),
            PHPUnit_Util_Class::getMethodParameters($method)
          ),
          'sigCall'  => PHPUnit_Util_Class::getMethodParameters($method, TRUE)
        );
    }
}

ksort($methods);

$buffer = '';

foreach ($methods as $name => $data) {
    $buffer .= sprintf(
      "\n\n%s\nfunction %s(%s)\n{\n    PHPUnit_Framework_Assert::%s(%s);\n}",
      str_replace('    ', '', $data['docblock']),
      $name,
      $data['sigDecl'],
      $name,
      $data['sigCall']
    );
}

$template = new Text_Template('PHPUnit/Framework/Assert/Functions.php.in');
$template->setVar(array('functions' => $buffer));
$template->renderTo('PHPUnit/Framework/Assert/Functions.php');
