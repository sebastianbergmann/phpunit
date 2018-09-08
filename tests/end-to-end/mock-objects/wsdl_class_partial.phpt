--TEST--
\PHPUnit\Framework\MockObject\Generator::generateClassFromWsdl('GoogleSearch.wsdl', 'GoogleSearch', array('doGoogleSearch'))
--SKIPIF--
<?php
if (!extension_loaded('soap')) echo 'skip: SOAP extension is required';
?>
--FILE--
<?php
require __DIR__ . '/../../../vendor/autoload.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

print $generator->generateClassFromWsdl(
    __DIR__ . '/../../_files/GoogleSearch.wsdl',
    'GoogleSearch',
    array('doGoogleSearch')
);
?>
--EXPECTF--
class GoogleSearch extends \SoapClient
{
    public function __construct($wsdl, array $options)
    {
        parent::__construct('%s/GoogleSearch.wsdl', $options);
    }

    public function doGoogleSearch($key, $q, $start, $maxResults, $filter, $restrict, $safeSearch, $lr, $ie, $oe)
    {
    }
}
