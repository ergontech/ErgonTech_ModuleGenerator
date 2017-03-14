<?php

namespace test;

class ErgonTech_Module_etc_ConfigTest extends \MageTest_PHPUnit_Framework_TestCase
{
    /**
     * @var \Mage_Core_Model_Config_Base
     */
    protected $config;

    public function setUp()
    {
        $this->config = new \Mage_Core_Model_Config_Base(__DIR__ . '/../../../../../../../app/code/community/ErgonTech/Module/etc/config.xml');
    }

    public function testModuleDeclaration()
    {
        $module = $this->config->getNode('modules/ErgonTech_Module');

        static::assertEquals('ErgonTech_Module', $module->getName());

        static::assertTrue(version_compare($module->version, '0.0.0', '>='));
    }
}