<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit07f5381980dd0db37a5dc1e460149cbc
{
    public static $files = array (
        '320cde22f66dd4f5d3fd621d3e88b98f' => __DIR__ . '/..' . '/symfony/polyfill-ctype/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Polyfill\\Ctype\\' => 23,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
            'PhpOption\\' => 10,
        ),
        'M' => 
        array (
            'Monolog\\' => 8,
        ),
        'J' => 
        array (
            'JiraRestApi\\' => 12,
        ),
        'D' => 
        array (
            'Dotenv\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Polyfill\\Ctype\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-ctype',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'PhpOption\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpoption/phpoption/src/PhpOption',
        ),
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
        'JiraRestApi\\' => 
        array (
            0 => __DIR__ . '/..' . '/lesstif/php-jira-rest-client/src',
        ),
        'Dotenv\\' => 
        array (
            0 => __DIR__ . '/..' . '/vlucas/phpdotenv/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'J' => 
        array (
            'JsonMapper' => 
            array (
                0 => __DIR__ . '/..' . '/netresearch/jsonmapper/src',
            ),
        ),
        'H' => 
        array (
            'HelpScoutApp\\' => 
            array (
                0 => __DIR__ . '/..' . '/helpscout/apps/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit07f5381980dd0db37a5dc1e460149cbc::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit07f5381980dd0db37a5dc1e460149cbc::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit07f5381980dd0db37a5dc1e460149cbc::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
