--TEST--
PHPUnit_Framework_MockObject_Generator::generateClassFromWsdl('GoogleSearch.wsdl', 'GoogleSearch')
--SKIPIF--
<?php
if (!extension_loaded('soap')) echo 'skip: SOAP extension is required';
?>
--FILE--
<?php
require_once 'PHPUnit/Autoload.php';
require_once 'Text/Template.php';

$generator = new PHPUnit_Framework_MockObject_Generator;

print $generator->generateClassFromWsdl(
  dirname(dirname(__FILE__)) . '/_files/GoogleSearch.wsdl',
  'GoogleSearch'
);
?>
--EXPECTF--
class GoogleSearch extends \SoapClient
{
    public function __construct($wsdl, array $options)
    {
        parent::__construct('%s/GoogleSearch.wsdl', $options);
    }

    public function doGetCachedPage($key, $url)
    {
    }

    public function doSpellingSuggestion($key, $phrase)
    {
    }

    public function doGoogleSearch($key, $q, $start, $maxResults, $filter, $restrict, $safeSearch, $lr, $ie, $oe)
    {
    }
}
