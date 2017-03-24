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
            $defaultResolver->addTemplatePath(__DIR__ . '/vendor/ergontech/mage-scaffold/templates');
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

        // Clean up these files
        $filesystem = static::getFilesystem();
        array_filter(require __DIR__ . '/cleanupfiles.php', [$filesystem, 'delete']);
        $filesystem->delete(basename(__FILE__)); // THIS WILL SELF-DESTRUCT!

        static::getModuleScaffolder()->generate($moduleName, $isCommunity, true, $version);
    }

    public static function validateModuleName($moduleName)
    {
        if (substr_count($moduleName, '_') === 1) {
            return $moduleName;
        }

        throw new ModuleNameException('A valid module name should have the following format: VendorName_ModuleName');
    }

    public static function validateModuleVersion($version)
    {
        if (version_compare($version, '0.0.0.0', '>=')) {
            return $version;
        }

        throw new VersionValidationException('A valid version must follow version_compare\'s logic: http://php.net/manual/en/function.version-compare.php');
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