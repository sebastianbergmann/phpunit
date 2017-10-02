<?php
require __DIR__ . '/Fixtures/FunctionCallback.php';

// root
class_alias('PHPUnit\Framework\MockObject\Generator', 'PHPUnit_Framework_MockObject_Generator');
class_alias('PHPUnit\Framework\MockObject\Invocation', 'PHPUnit_Framework_MockObject_Invocation');
class_alias('PHPUnit\Framework\MockObject\InvocationMocker', 'PHPUnit_Framework_MockObject_InvocationMocker');
class_alias('PHPUnit\Framework\MockObject\Invokable', 'PHPUnit_Framework_MockObject_Invokable');
class_alias('PHPUnit\Framework\MockObject\Matcher', 'PHPUnit_Framework_MockObject_Matcher');
class_alias('PHPUnit\Framework\MockObject\MockBuilder', 'PHPUnit_Framework_MockObject_MockBuilder');
class_alias('PHPUnit\Framework\MockObject\MockObject', 'PHPUnit_Framework_MockObject_MockObject');
class_alias('PHPUnit\Framework\MockObject\MockObject', 'MockObject');
class_alias('PHPUnit\Framework\MockObject\Stub', 'PHPUnit_Framework_MockObject_Stub');
class_alias('PHPUnit\Framework\MockObject\Verifiable', 'PHPUnit_Framework_MockObject_Verifiable');

// Builder
class_alias('PHPUnit\Framework\MockObject\Builder\Identity', 'PHPUnit_Framework_MockObject_Builder_Identity');
class_alias('PHPUnit\Framework\MockObject\Builder\InvocationMocker', 'PHPUnit_Framework_MockObject_Builder_InvocationMocker');
class_alias('PHPUnit\Framework\MockObject\Builder\Match', 'PHPUnit_Framework_MockObject_Builder_Match');
class_alias('PHPUnit\Framework\MockObject\Builder\MethodNameMatch', 'PHPUnit_Framework_MockObject_Builder_MethodNameMatch');
class_alias('PHPUnit\Framework\MockObject\Builder\NamespaceMatch', 'PHPUnit_Framework_MockObject_Builder_Namespace');
class_alias('PHPUnit\Framework\MockObject\Builder\ParametersMatch', 'PHPUnit_Framework_MockObject_Builder_ParametersMatch');
class_alias('PHPUnit\Framework\MockObject\Builder\Stub', 'PHPUnit_Framework_MockObject_Builder_Stub');

// Exception
class_alias('PHPUnit\Framework\MockObject\Exception\BadMethodCallException', 'PHPUnit_Framework_MockObject_BadMethodCallException');
class_alias('PHPUnit\Framework\MockObject\Exception\Exception', 'PHPUnit_Framework_MockObject_Exception');
class_alias('PHPUnit\Framework\MockObject\Exception\RuntimeException', 'PHPUnit_Framework_MockObject_RuntimeException');

// Generator

// Invocation
class_alias('PHPUnit\Framework\MockObject\Invocation\ObjectInvocation', 'PHPUnit_Framework_MockObject_Invocation_Object');
class_alias('PHPUnit\Framework\MockObject\Invocation\StaticInvocation', 'PHPUnit_Framework_MockObject_Invocation_Static');

// Matcher
class_alias('PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount', 'PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount');
class_alias('PHPUnit\Framework\MockObject\Matcher\AnyParameters', 'PHPUnit_Framework_MockObject_Matcher_AnyParameters');
class_alias('PHPUnit\Framework\MockObject\Matcher\ConsecutiveParameters', 'PHPUnit_Framework_MockObject_Matcher_ConsecutiveParameters');
class_alias('PHPUnit\Framework\MockObject\Matcher\Invocation', 'PHPUnit_Framework_MockObject_Matcher_Invocation');
class_alias('PHPUnit\Framework\MockObject\Matcher\InvokedAtIndex', 'PHPUnit_Framework_MockObject_Matcher_InvokedAtIndex');
class_alias('PHPUnit\Framework\MockObject\Matcher\InvokedAtLeastCount', 'PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastCount');
class_alias('PHPUnit\Framework\MockObject\Matcher\InvokedAtLeastOnce', 'PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce');
class_alias('PHPUnit\Framework\MockObject\Matcher\InvokedAtMostCount', 'PHPUnit_Framework_MockObject_Matcher_InvokedAtMostCount');
class_alias('PHPUnit\Framework\MockObject\Matcher\InvokedCount', 'PHPUnit_Framework_MockObject_Matcher_InvokedCount');
class_alias('PHPUnit\Framework\MockObject\Matcher\InvokedRecorder', 'PHPUnit_Framework_MockObject_Matcher_InvokedRecorder');
class_alias('PHPUnit\Framework\MockObject\Matcher\MethodName', 'PHPUnit_Framework_MockObject_Matcher_MehodName');
class_alias('PHPUnit\Framework\MockObject\Matcher\Parameters', 'PHPUnit_Framework_MockObject_Matcher_Parameters');
class_alias('PHPUnit\Framework\MockObject\Matcher\StatelessInvocation', 'PHPUnit_Framework_MockObject_Matcher_StatelessInvocation');

// Stub
class_alias('PHPUnit\Framework\MockObject\Stub\ConsecutiveCalls', 'PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls');
class_alias('PHPUnit\Framework\MockObject\Stub\Exception', 'PHPUnit_Framework_MockObject_Stub_Exception');
class_alias('PHPUnit\Framework\MockObject\Stub\MatcherCollection', 'PHPUnit_Framework_MockObject_Stub_MatcherCollection');
class_alias('PHPUnit\Framework\MockObject\Stub\ReturnArgument', 'PHPUnit_Framework_MockObject_Stub_ReturnArgument');
class_alias('PHPUnit\Framework\MockObject\Stub\ReturnCallback', 'PHPUnit_Framework_MockObject_Stub_ReturnCallback');
class_alias('PHPUnit\Framework\MockObject\Stub\ReturnReference', 'PHPUnit_Framework_MockObject_Stub_ReturnReference');
class_alias('PHPUnit\Framework\MockObject\Stub\ReturnSelf', 'PHPUnit_Framework_MockObject_Stub_ReturnSelf');
class_alias('PHPUnit\Framework\MockObject\Stub\ReturnStub', 'PHPUnit_Framework_MockObject_Stub_Return');
class_alias('PHPUnit\Framework\MockObject\Stub\ReturnValueMap', 'PHPUnit_Framework_MockObject_Stub_ReturnValueMap');

require __DIR__ . '/../vendor/autoload.php';
