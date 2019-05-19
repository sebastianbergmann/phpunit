--TEST--
\PHPUnit\Framework\MockObject\Generator::generateClassFromWsdl('3530.wsdl', 'Test')
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('soap')) echo 'skip: SOAP extension is required';
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../../../vendor/autoload.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

print $generator->generateClassFromWsdl(
    __DIR__ . '/../../../_files/3530.wsdl',
    'Test'
);
--EXPECTF--
declare(strict_types=1);

class Test extends \SoapClient
{
    public function __construct($wsdl, array $options)
    {
        parent::__construct('%s/3530.wsdl', $options);
    }

    public function Contact_Information($Contact_Id)
    {
    }
}
