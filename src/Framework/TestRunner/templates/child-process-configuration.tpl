ConfigurationRegistry::loadFrom('{serializedConfiguration}');

DifferBuilder::configureComparatorFactory();

if ('{sourceMapFile}' !== '') {
    SourceMapper::loadFrom('{sourceMapFile}', ConfigurationRegistry::get()->source());
}

(new PhpHandler)->handle(ConfigurationRegistry::get()->php());

if ('{bootstrap}' !== '') {
    require_once '{bootstrap}';
}

$__phpunit_includeTestSuites = ConfigurationRegistry::get()->includeTestSuites();
$__phpunit_excludeTestSuites = ConfigurationRegistry::get()->excludeTestSuites();

foreach (ConfigurationRegistry::get()->bootstrapForTestSuite() as $__phpunit_testSuiteName => $__phpunit_bootstrapForTestSuite) {
    if ($__phpunit_includeTestSuites !== [] && !in_array($__phpunit_testSuiteName, $__phpunit_includeTestSuites, true)) {
        continue;
    }

    if ($__phpunit_excludeTestSuites !== [] && in_array($__phpunit_testSuiteName, $__phpunit_excludeTestSuites, true)) {
        continue;
    }

    require_once $__phpunit_bootstrapForTestSuite;
}
