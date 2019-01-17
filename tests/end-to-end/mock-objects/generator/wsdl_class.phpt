--TEST--
\PHPUnit\Framework\MockObject\Generator::generateClassFromWsdl('GoogleSearch.wsdl', 'GoogleSearch')
--SKIPIF--
<?php
if (!extension_loaded('soap')) echo 'skip: SOAP extension is required';
?>
--FILE--
<?php declare(strict_types=1);
require __DIR__ . '/../../../../vendor/autoload.php';

$generator = new \PHPUnit\Framework\MockObject\Generator;

print $generator->generateClassFromWsdl(
    __DIR__ . '/../../../_files/GoogleSearch.wsdl',
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

    public function doGoogleSearch($key, $q, $start, $maxResults, $filter, $restrict, $safeSearch, $lr, $ie, $oe)
    {
    }

    public function doGetCachedPage($key, $url)
    {
    }

    public function doSpellingSuggestion($key, $phrase)
    {
    }
}
