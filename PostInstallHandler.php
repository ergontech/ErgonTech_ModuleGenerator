<?php

namespace ErgonTech\ModuleGenerator;

use Composer\Script\Event;
use Mpw\MageScaffold\ModuleScaffolder;
use Phly\Mustache\Resolver\AggregateResolver;
use Phly\Mustache\Resolver\DefaultResolver;

class PostInstallHandler
{
    /**
     * @var ModuleScaffolder
     */
    private static $moduleScaffolder;

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
                    new \League\Flysystem\Filesystem(new \League\Flysystem\Adapter\Local('./tmp'))));
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

        $moduleName = $io->ask('Enter Module Name: ');
        $isCommunity = $io->askConfirmation('Community Code Pool [<comment>yes</comment>]? ', true);
        $version = $io->ask('Enter Module Version: [<comment>0.1.0</comment>] ', '0.1.0');

        static::guardAgainstInvalidModuleNames($moduleName);

        static::getModuleScaffolder()->generate($moduleName, $isCommunity, true, $version);
    }

    private static function guardAgainstInvalidModuleNames($name)
    {
        if (substr_count($name, '_') !== 1) {
            throw new ModuleNameException('A valid module name should have the following format: VendorName_ModuleName');
        }
    }

    public static function validateModuleName($moduleName)
    {
        return substr_count($moduleName, '_') === 1;
    }

    public static function validateModuleVersion($version)
    {
        return version_compare($version, '0.0.0.0', '>=');
    }
}