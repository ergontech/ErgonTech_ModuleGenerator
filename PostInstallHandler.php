<?php

namespace ErgonTech\ModuleGenerator;

use Composer\Script\Event;

class PostInstallHandler
{
    private $codePools = ['community', 'local'];

    public function promptForModuleInformation(Event $event)
    {
        $io = $event->getIO();

        $moduleName = $io->ask('Enter Module name');
        $codePool = $io->select('Choose Code Pool', $this->codePools, 'community');

        $this->guardAgainstInvalidModuleNames($moduleName);
        $this->guardAgainstInvalidCodePool($codePool);
    }

    private function guardAgainstInvalidModuleNames($name)
    {
        if (substr_count($name, '_') !== 1) {
            throw new ModuleNameException('A valid module name should have the following format: VendorName_ModuleName');
        }
    }

    /**
     * @param $codePool
     * @throws CodePoolChoiceException
     */
    private function guardAgainstInvalidCodePool($codePool)
    {
        if (!in_array($codePool, $this->codePools)) {
            throw new CodePoolChoiceException('The codepool must be one of the displayed options');
        }
    }
}