#!/usr/bin/env php
<?php
require dirname(__DIR__) . '/PHPUnit/Autoload.php';

$buffer  = '';
$class   = new ReflectionClass('PHPUnit_Framework_Assert');
$methods = array();

foreach ($class->getMethods() as $method) {
    $docblock = $method->getDocComment();
    $name     = $method->getName();

    if (strpos($name, 'assert') === 0 ||
        strpos($docblock, '@return PHPUnit_Framework_Constraint') !== FALSE) {
        $methods[$name] = array(
          'class'    => 'PHPUnit_Framework_Assert',
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

$class = new ReflectionClass('PHPUnit_Framework_TestCase');

foreach ($class->getMethods() as $method) {
    $docblock = $method->getDocComment();
    $name     = $method->getName();

    if (strpos($docblock, '@return PHPUnit_Framework_MockObject_Matcher') !== FALSE ||
        strpos($docblock, '@return PHPUnit_Framework_MockObject_Stub') !== FALSE) {
        $methods[$name] = array(
          'class'    => 'PHPUnit_Framework_TestCase',
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

foreach ($methods as $name => $data) {
    $buffer .= sprintf(
      "\n\n%s\nfunction %s(%s)\n{\n    return call_user_func_array(\n      '%s::%s',\n      func_get_args()\n    );\n}",
      str_replace('    ', '', $data['docblock']),
      $name,
      $data['sigDecl'],
      $data['class'],
      $name,
      $data['sigCall']
    );
}

$template = new Text_Template(dirname(__DIR__) . '/PHPUnit/Framework/Assert/Functions.php.in');
$template->setVar(array('functions' => $buffer));
$template->renderTo(dirname(__DIR__) . '/PHPUnit/Framework/Assert/Functions.php');
