<?php

namespace ErgonTech\ModuleGenerator;

use Composer\Script\Event;
use League\Flysystem\Filesystem;
use Mpw\MageScaffold\ModuleScaffolder;
use Phly\Mustache\Resolver\AggregateResolver;
use Phly\Mustache\Resolver\DefaultResolver;

class PostInstallHandler
{
    /**
     * @var ModuleScaffolder
     */
    private static $moduleScaffolder;

    /**
     * @var Filesystem
     */
    private static $filesystem;

    public static function getModuleScaffolder()
    {
        if (!isset(static::$moduleScaffolder)) {
            $res = new AggregateResolver();
            $defaultResolver = new DefaultResolver();
            $defaultResolver->addTemplatePath(__DIR__ . '/vendor/mpw/mage-scaffold/templates');
            $res->attach($defaultResolver);

            static::$moduleScaffolder = new \Mpw\MageScaffold\ModuleScaffolder(
                new \Mpw\MageScaffold\FileScaffolder(
                    new \Phly\Mustache\Mustache($res),
                    static::getFilesystem()));
        }

        return static::$moduleScaffolder;
    }

    public static function setModuleScaffolder(ModuleScaffolder $moduleScaffolder)
    {
        static::$moduleScaffolder = $moduleScaffolder;
    }

    public static function promptForModuleInformation(Event $event)
    {
        $io = $event->getIO();

        $moduleName = $io->askAndValidate('Enter Module Name: ', [static::class, 'validateModuleName']);
        $isCommunity = $io->askConfirmation('Community Code Pool [<comment>yes</comment>]? ', true);
        $version = $io->askAndValidate('Enter Module Version: [<comment>0.1.0</comment>] ', [static::class, 'validateModuleVersion'], null, '0.1.0');

        $filesystem = static::getFilesystem();
        $filesystem->delete('composer.lock');
        $filesystem->delete('composer.json');
        $filesystem->delete('phpunit.xml.dist');
        $filesystem->delete('ModuleNameException.php');
        $filesystem->delete('test/PostInstallHandlerTest.php');
        static::getModuleScaffolder()->generate($moduleName, $isCommunity, true, $version);
        $filesystem->delete('PostInstallHandler.php');
    }

    public static function validateModuleName($moduleName)
    {
        return substr_count($moduleName, '_') === 1 ? $moduleName : false;
    }

    public static function validateModuleVersion($version)
    {
        return version_compare($version, '0.0.0.0', '>=') ? $version : false;
    }

    public static function getFilesystem()
    {
        if (!isset(static::$filesystem)) {
            static::$filesystem = new \League\Flysystem\Filesystem(new \League\Flysystem\Adapter\Local('.'));
        }

        return static::$filesystem;
    }

    public static function setFilesystem(Filesystem $filesystem)
    {
        static::$filesystem = $filesystem;
    }

}