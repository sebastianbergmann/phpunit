<?php return array(
    'root' => array(
        'name' => '__root__',
        'pretty_version' => '11.5.x-dev',
        'version' => '11.5.9999999.9999999-dev',
        'reference' => '04669f839e9277f3e956438b79ad3a0a9f2e611b',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        '__root__' => array(
            'pretty_version' => '11.5.x-dev',
            'version' => '11.5.9999999.9999999-dev',
            'reference' => '04669f839e9277f3e956438b79ad3a0a9f2e611b',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'friendsofphp/php-cs-fixer' => array(
            'dev_requirement' => true,
            'replaced' => array(
                0 => 'v3.75.0',
            ),
        ),
        'kubawerlos/php-cs-fixer-custom-fixers' => array(
            'pretty_version' => 'v3.27.0',
            'version' => '3.27.0.0',
            'reference' => 'd860473d16b906c7945206177edc7d112357a706',
            'type' => 'library',
            'install_path' => __DIR__ . '/../kubawerlos/php-cs-fixer-custom-fixers',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'php-cs-fixer/shim' => array(
            'pretty_version' => 'v3.75.0',
            'version' => '3.75.0.0',
            'reference' => 'eea219a577085bd13ff0cb644a422c20798316c7',
            'type' => 'application',
            'install_path' => __DIR__ . '/../php-cs-fixer/shim',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
    ),
);
