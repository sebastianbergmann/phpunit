<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2011, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    PHPUnit_MockObject
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2011 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://github.com/sebastianbergmann/phpunit-mock-objects
 * @since      File available since Release 1.1.0
 */

spl_autoload_register(
  function ($class)
  {
      static $classes = NULL;
      static $path = NULL;

      if ($classes === NULL) {
          $classes = array(
            'phpunit_framework_mockobject_builder_identity' => '/Framework/MockObject/Builder/Identity.php',
            'phpunit_framework_mockobject_builder_invocationmocker' => '/Framework/MockObject/Builder/InvocationMocker.php',
            'phpunit_framework_mockobject_builder_match' => '/Framework/MockObject/Builder/Match.php',
            'phpunit_framework_mockobject_builder_methodnamematch' => '/Framework/MockObject/Builder/MethodNameMatch.php',
            'phpunit_framework_mockobject_builder_namespace' => '/Framework/MockObject/Builder/Namespace.php',
            'phpunit_framework_mockobject_builder_parametersmatch' => '/Framework/MockObject/Builder/ParametersMatch.php',
            'phpunit_framework_mockobject_builder_stub' => '/Framework/MockObject/Builder/Stub.php',
            'phpunit_framework_mockobject_generator' => '/Framework/MockObject/Generator.php',
            'phpunit_framework_mockobject_invocation' => '/Framework/MockObject/Invocation.php',
            'phpunit_framework_mockobject_invocation_object' => '/Framework/MockObject/Invocation/Object.php',
            'phpunit_framework_mockobject_invocation_static' => '/Framework/MockObject/Invocation/Static.php',
            'phpunit_framework_mockobject_invocationmocker' => '/Framework/MockObject/InvocationMocker.php',
            'phpunit_framework_mockobject_invokable' => '/Framework/MockObject/Invokable.php',
            'phpunit_framework_mockobject_matcher' => '/Framework/MockObject/Matcher.php',
            'phpunit_framework_mockobject_matcher_anyinvokedcount' => '/Framework/MockObject/Matcher/AnyInvokedCount.php',
            'phpunit_framework_mockobject_matcher_anyparameters' => '/Framework/MockObject/Matcher/AnyParameters.php',
            'phpunit_framework_mockobject_matcher_invocation' => '/Framework/MockObject/Matcher/Invocation.php',
            'phpunit_framework_mockobject_matcher_invokedatindex' => '/Framework/MockObject/Matcher/InvokedAtIndex.php',
            'phpunit_framework_mockobject_matcher_invokedatleastonce' => '/Framework/MockObject/Matcher/InvokedAtLeastOnce.php',
            'phpunit_framework_mockobject_matcher_invokedcount' => '/Framework/MockObject/Matcher/InvokedCount.php',
            'phpunit_framework_mockobject_matcher_invokedrecorder' => '/Framework/MockObject/Matcher/InvokedRecorder.php',
            'phpunit_framework_mockobject_matcher_methodname' => '/Framework/MockObject/Matcher/MethodName.php',
            'phpunit_framework_mockobject_matcher_parameters' => '/Framework/MockObject/Matcher/Parameters.php',
            'phpunit_framework_mockobject_matcher_statelessinvocation' => '/Framework/MockObject/Matcher/StatelessInvocation.php',
            'phpunit_framework_mockobject_mockbuilder' => '/Framework/MockObject/MockBuilder.php',
            'phpunit_framework_mockobject_mockobject' => '/Framework/MockObject/MockObject.php',
            'phpunit_framework_mockobject_stub' => '/Framework/MockObject/Stub.php',
            'phpunit_framework_mockobject_stub_consecutivecalls' => '/Framework/MockObject/Stub/ConsecutiveCalls.php',
            'phpunit_framework_mockobject_stub_exception' => '/Framework/MockObject/Stub/Exception.php',
            'phpunit_framework_mockobject_stub_matchercollection' => '/Framework/MockObject/Stub/MatcherCollection.php',
            'phpunit_framework_mockobject_stub_return' => '/Framework/MockObject/Stub/Return.php',
            'phpunit_framework_mockobject_stub_returnargument' => '/Framework/MockObject/Stub/ReturnArgument.php',
            'phpunit_framework_mockobject_stub_returncallback' => '/Framework/MockObject/Stub/ReturnCallback.php',
            'phpunit_framework_mockobject_stub_returnself' => '/Framework/MockObject/Stub/ReturnSelf.php',
            'phpunit_framework_mockobject_stub_returnvaluemap' => '/Framework/MockObject/Stub/ReturnValueMap.php',
            'phpunit_framework_mockobject_verifiable' => '/Framework/MockObject/Verifiable.php'
          );

          $path = dirname(dirname(dirname(__FILE__)));
      }

      $cn = strtolower($class);

      if (isset($classes[$cn])) {
          require $path . $classes[$cn];
      }
  }
);
