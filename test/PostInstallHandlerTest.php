<?php
/**
 * Created by IntelliJ IDEA.
 * User: matthewwells
 * Date: 3/16/17
 * Time: 17:50
 */

namespace test\ErgonTech\ModuleGenerator;


use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Composer\Script\Event;
use ErgonTech\ModuleGenerator\PostInstallHandler;
use League\Flysystem\Filesystem;
use Mpw\MageScaffold\ModuleScaffolder;


class PostInstallHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Event|\PHPUnit_Framework_MockObject_MockObject
     */
    private $event;

    /**
     * @var IOInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $io;

    /**
     * @var ModuleScaffolder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleScaffolder;

    protected function setUp()
    {
        parent::setUp();
        $this->event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIo'
            ])
            ->getMock();

        $this->io = $this->getMockBuilder(NullIO::class)
            ->setMethods([
                'askAndValidate', 'askConfirmation'
            ])
            ->getMock();

        $this->event->expects(static::any())
            ->method('getIo')
            ->willReturn($this->io);

        $this->moduleScaffolder = $this->getMockBuilder(ModuleScaffolder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'generate'
            ])
            ->getMock();

        PostInstallHandler::setModuleScaffolder($this->moduleScaffolder);
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        PostInstallHandler::setFilesystem($filesystem);
    }

    public function testModuleNameValidation()
    {
        static::assertFalse(PostInstallHandler::validateModuleName('asdf'));
        static::assertTrue(PostInstallHandler::validateModuleName('Asdf_Asdf'));
    }

    public function testVersionStringValidation()
    {
        static::assertFalse(PostInstallHandler::validateModuleVersion('asdf'));
        static::assertTrue(PostInstallHandler::validateModuleVersion('0.1.0'));
        static::assertTrue(PostInstallHandler::validateModuleVersion('1.0'));
        static::assertTrue(PostInstallHandler::validateModuleVersion('1'));
    }

    public function testValidInput()
    {

        $this->io->expects(static::any())
            ->method('askAndValidate')
            ->withConsecutive(['Enter Module Name: ', static::isType('callable')], ['Enter Module Version: [<comment>0.1.0</comment>] ', static::isType('callable'), null, '0.1.0'])
            ->willReturnCallback(function ($prompt, $cb, $attempts, $default) {
                if (($prompt === 'Enter Module Name: ') && is_callable($cb)) {
                    return 'Asdf_Asdf';
                }

                if (($prompt === 'Enter Module Version: [<comment>0.1.0</comment>] ') && is_callable($cb)) {
                    return '0.1.0';
                }

                return false;
            });

        $this->io->expects(static::once())
            ->method('askConfirmation')
            ->with('Community Code Pool [<comment>yes</comment>]? ', true)
            ->willReturn(true);

        $this->moduleScaffolder->expects(static::once())
            ->method('generate')
            ->with(
                'Asdf_Asdf',
                true,
                true,
                '0.1.0');

        PostInstallHandler::promptForModuleInformation($this->event);
    }
}
